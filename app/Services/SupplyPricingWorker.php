<?php

declare(strict_types=1);

namespace GordonFoodService\App\Services;

use InvalidArgumentException;
use PDO;

class SupplyPricingWorker
{
    private PDO $pdo;

    // Default per-kg prices (used if database has no prices)
    public const DEFAULT_PRICES_PER_KG = [
        'water' => 0.85,
        'dry_food' => 3.50,
        'canned_food' => 4.25,
        'mixed_supplies' => 5.75,
        'toiletries' => 8.50,
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get per-kg prices from database
     */
    public function getPricesPerKg(): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
            $stmt->execute(['supply_prices']);
            $json = $stmt->fetchColumn();
            if ($json) {
                $prices = json_decode($json, true);
                if (is_array($prices) && !empty($prices)) {
                    $result = [];
                    foreach ($prices as $key => $item) {
                        $result[$key] = (float)($item['price'] ?? 0);
                    }
                    return $result;
                }
            }
        } catch (\Throwable $e) {
        }
        return self::DEFAULT_PRICES_PER_KG;
    }

    /**
     * @param array{duration_days:int,crew_size:int,supply_types:array,delivery_location:string,delivery_speed:string,storage_life_months?:int|null} $input
     * @param array{discount_percent?:int|float|null,discount_eligible?:int|bool|null,active?:int|bool|null} $contractor
     * @return array{base_price:float,discount_percent:float,calculated_price:float,currency:string}
     */
    public function calculate(array $input, array $contractor): array
    {
        $durationDays = (int)($input['duration_days'] ?? 0);
        if ($durationDays < 14) {
            throw new InvalidArgumentException('Minimum supply duration is 14 days.');
        }

        $crewSize = (int)($input['crew_size'] ?? 0);
        if ($crewSize < 1) {
            throw new InvalidArgumentException('Crew size must be at least 1.');
        }

        $supplyTypes = $input['supply_types'] ?? [];
        if (!is_array($supplyTypes) || count($supplyTypes) < 1) {
            throw new InvalidArgumentException('Select at least one supply type.');
        }

        $deliveryLocation = (string)($input['delivery_location'] ?? 'onshore');
        $deliverySpeed = (string)($input['delivery_speed'] ?? 'standard');
        $storageLifeMonths = $input['storage_life_months'] !== null ? (int)($input['storage_life_months'] ?? 6) : 6;
        if ($storageLifeMonths < 1) {
            $storageLifeMonths = 1;
        }

        $config = $this->getConfig();

        $baseRate = (float)($config['base_rate_per_person_day'] ?? 22.5);

        $typeMultipliers = $config['type_multipliers'] ?? [
            'water' => 0.9,
            'dry_food' => 1.0,
            'canned_food' => 1.05,
            'mixed_supplies' => 1.1,
            'toiletries' => 1.15,
        ];

        $selectedTypeMultipliers = [];
        foreach ($supplyTypes as $t) {
            $t = (string)$t;
            if (isset($typeMultipliers[$t])) {
                $selectedTypeMultipliers[] = (float)$typeMultipliers[$t];
            }
        }
        if (count($selectedTypeMultipliers) < 1) {
            throw new InvalidArgumentException('Invalid supply type selection.');
        }
        $typeMultiplier = array_sum($selectedTypeMultipliers) / count($selectedTypeMultipliers);

        $locationMultipliers = $config['location_multipliers'] ?? [
            'pickup' => 0.85,
            'local' => 0.95,
            'onshore' => 1.0,
            'nearshore' => 1.15,
            'offshore_rig' => 1.35,
        ];
        if (!isset($locationMultipliers[$deliveryLocation])) {
            throw new InvalidArgumentException('Invalid delivery location.');
        }
        $locationMultiplier = (float)$locationMultipliers[$deliveryLocation];

        $speedMultipliers = $config['speed_multipliers'] ?? [
            'standard' => 1.0,
            'priority' => 1.2,
            'emergency' => 1.45,
        ];
        if (!isset($speedMultipliers[$deliverySpeed])) {
            throw new InvalidArgumentException('Invalid delivery speed.');
        }
        $speedMultiplier = (float)$speedMultipliers[$deliverySpeed];

        $storageMultiplier = 1.0;
        if ($storageLifeMonths >= 12) {
            $storageMultiplier = 1.1;
        } elseif ($storageLifeMonths >= 6) {
            $storageMultiplier = 1.05;
        }

        // Check if per-kg quantities are provided (new pricing model)
        $supplyQuantities = $input['supply_quantities'] ?? [];
        $itemBreakdown = [];
        
        // Get prices from database
        $dbPrices = $this->getPricesPerKg();
        
        if (!empty($supplyQuantities) && is_array($supplyQuantities)) {
            // New per-kg pricing model
            $basePrice = 0.0;
            foreach ($supplyQuantities as $type => $kg) {
                $kg = (float)$kg;
                if ($kg > 0 && isset($dbPrices[$type])) {
                    $pricePerKg = $dbPrices[$type];
                    $itemTotal = $kg * $pricePerKg;
                    $itemBreakdown[$type] = [
                        'kg' => $kg,
                        'price_per_kg' => $pricePerKg,
                        'subtotal' => round($itemTotal, 2),
                    ];
                    $basePrice += $itemTotal;
                }
            }
            if ($basePrice <= 0) {
                throw new InvalidArgumentException('Please specify quantity for at least one supply type.');
            }
            // Apply location, speed, and storage multipliers to the per-kg total
            $basePrice = $basePrice * $locationMultiplier * $speedMultiplier * $storageMultiplier;
            $basePrice = round($basePrice, 2);
        } else {
            // Legacy pricing model (crew size × duration × base rate)
            $basePrice = $baseRate * $crewSize * $durationDays * $typeMultiplier * $locationMultiplier * $speedMultiplier * $storageMultiplier;
            $basePrice = round($basePrice, 2);
        }

        $discountPercent = 0.0;
        $isActive = !empty($contractor['active']);
        $isEligible = !empty($contractor['discount_eligible']);
        if ($isActive && $isEligible) {
            $discountPercent = (float)($contractor['discount_percent'] ?? 0);
            if ($discountPercent < 0) {
                $discountPercent = 0;
            }
            if ($discountPercent > 75) {
                $discountPercent = 75;
            }
        }

        $finalPrice = $basePrice;
        if ($discountPercent > 0) {
            $finalPrice = round($basePrice * (1 - ($discountPercent / 100)), 2);
        }

        return [
            'base_price' => $basePrice,
            'discount_percent' => $discountPercent,
            'calculated_price' => $finalPrice,
            'currency' => 'USD',
            'item_breakdown' => $itemBreakdown,
        ];
    }

    /**
     * @return array{base_rate_per_person_day?:float,type_multipliers?:array,location_multipliers?:array,speed_multipliers?:array}
     */
    private function getConfig(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT config_json FROM supply_pricing_config ORDER BY id DESC LIMIT 1');
            $json = $stmt ? $stmt->fetchColumn() : null;
            if (is_string($json) && $json !== '') {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
        }

        return [];
    }
}
