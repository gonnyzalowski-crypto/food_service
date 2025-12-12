<?php
$tracking = $_GET['tracking'] ?? '';
$lang = $_SESSION['lang'] ?? 'de';
?>
<div class="page-header">
  <h1><?= __('track_your_shipment') ?></h1>
  <p class="page-subtitle"><?= __('enter_tracking') ?></p>
</div>

<div class="card" style="max-width: 600px;">
  <div class="card-body">
    <form id="track-form">
      <div class="form-group">
        <label for="tracking" class="form-label"><?= __('tracking_number') ?></label>
        <input type="text" id="tracking" name="tracking" class="form-control" placeholder="<?= __('enter_tracking_number') ?>" value="<?= htmlspecialchars($tracking) ?>">
      </div>
      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;"><?= __('track') ?></button>
    </form>
  </div>
</div>

<div id="result" style="margin-top: 24px;"></div>

<script>
const form = document.getElementById('track-form');
const resultDiv = document.getElementById('result');
const lang = '<?= $lang ?>';

const translations = {
  loading: { en: 'Loading tracking data...', de: 'Lade Tracking-Daten...' },
  error: { en: 'Error loading tracking data.', de: 'Fehler beim Laden der Tracking-Daten.' },
  not_found: { en: 'Shipment not found. Please check your tracking number.', de: 'Sendung nicht gefunden. Bitte überprüfen Sie Ihre Sendungsnummer.' },
  shipment: { en: 'Shipment', de: 'Sendung' },
  carrier: { en: 'Carrier', de: 'Spediteur' },
  status: { en: 'Status', de: 'Status' },
  origin: { en: 'Origin', de: 'Herkunft' },
  destination: { en: 'Destination', de: 'Ziel' },
  shipped_date: { en: 'Shipped Date', de: 'Versanddatum' },
  tracking_history: { en: 'Tracking History', de: 'Sendungsverlauf' },
  time: { en: 'Time', de: 'Zeit' },
  location: { en: 'Location', de: 'Ort' },
  description: { en: 'Description', de: 'Beschreibung' }
};

function t(key) {
  return translations[key] ? (translations[key][lang] || translations[key]['en']) : key;
}

async function fetchTracking(trackingNumber) {
  resultDiv.innerHTML = '<div class="card"><div class="card-body" style="text-align: center; padding: 32px;"><div style="font-size: 2rem;">⏳</div><p>' + t('loading') + '</p></div></div>';
  
  const res = await fetch('/api/track', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ tracking_number: trackingNumber })
  });
  
  if (!res.ok) {
    resultDiv.innerHTML = '<div class="alert alert-error">' + t('error') + '</div>';
    return;
  }
  
  const data = await res.json();
  if (!data || !data.tracking_number) {
    resultDiv.innerHTML = '<div class="card"><div class="card-body" style="text-align: center; padding: 48px;"><div style="font-size: 3rem; margin-bottom: 16px;">📦</div><h3>' + t('not_found') + '</h3></div></div>';
    return;
  }
  
  let html = '<div class="card" style="margin-bottom: 24px;">';
  html += '<div class="card-header"><h3 class="card-title">' + t('shipment') + ': ' + data.tracking_number + '</h3></div>';
  html += '<div class="card-body">';
  html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">';
  html += '<div><strong>' + t('carrier') + ':</strong><br>' + (data.carrier || '-') + '</div>';
  html += '<div><strong>' + t('status') + ':</strong><br><span class="order-status-badge status-' + (data.status || 'pending') + '">' + (data.status || '-') + '</span></div>';
  if (data.origin_city) html += '<div><strong>' + t('origin') + ':</strong><br>' + data.origin_city + (data.origin_country ? ', ' + data.origin_country : '') + '</div>';
  if (data.destination_city) html += '<div><strong>' + t('destination') + ':</strong><br>' + data.destination_city + (data.destination_country ? ', ' + data.destination_country : '') + '</div>';
  if (data.shipped_at) html += '<div><strong>' + t('shipped_date') + ':</strong><br>' + new Date(data.shipped_at).toLocaleDateString(lang === 'de' ? 'de-DE' : 'en-US') + '</div>';
  html += '</div></div></div>';
  
  if (Array.isArray(data.events) && data.events.length) {
    html += '<div class="card"><div class="card-header"><h3 class="card-title">' + t('tracking_history') + '</h3></div>';
    html += '<div class="card-body" style="padding: 0;"><table class="data-table"><thead><tr><th>' + t('time') + '</th><th>' + t('location') + '</th><th>' + t('status') + '</th><th>' + t('description') + '</th></tr></thead><tbody>';
    data.events.forEach(ev => {
      const date = ev.timestamp ? new Date(ev.timestamp).toLocaleString(lang === 'de' ? 'de-DE' : 'en-US') : (ev.ts || '');
      html += '<tr><td>' + date + '</td><td>' + (ev.location || '-') + '</td><td><span class="order-status-badge">' + (ev.status || ev.status_label || '-') + '</span></td><td>' + (ev.description || '-') + '</td></tr>';
    });
    html += '</tbody></table></div></div>';
  }
  
  resultDiv.innerHTML = html;
}

form.addEventListener('submit', function (e) {
  e.preventDefault();
  const tracking = document.getElementById('tracking').value.trim();
  if (tracking) {
    fetchTracking(tracking);
  }
});

<?php if ($tracking): ?>
fetchTracking('<?= addslashes($tracking) ?>');
<?php endif; ?>
</script>
