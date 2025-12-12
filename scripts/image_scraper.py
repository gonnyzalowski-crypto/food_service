#!/usr/bin/env python
"""
Industrial equipment image scraper.

Features
--------
- Reads product names from:
  * CSV file (recommended), or
  * built‑in PRODUCT_NAMES list
- Uses SerpAPI (Google Images) or Bing Image Search API
- Builds keyword‑reinforced queries:
  "{product_name} industrial equipment hydraulic mechanical OEM"
- Downloads 1–3 relevant images per product into:
  images/<product-slug>/
- Heuristically filters out:
  * obvious stock/watermarked sources
  * images likely showing people, logos, or clothing
- Retries across result pages if no valid images found
- Logs results to scrape_log.csv

IMPORTANT
---------
- This script does NOT invent any image URLs.
- All URLs come directly from the chosen search API at run time.
- Relevance & watermark filters are best‑effort heuristics only.
"""

import argparse
import csv
import logging
import os
import re
import sys
import time
from pathlib import Path
from typing import Dict, Iterable, List, Optional, Tuple
from urllib.parse import urlparse

import requests

# ---------------------------------------------------------------------------
# Configuration / constants
# ---------------------------------------------------------------------------

# Fallback product list if no CSV is provided.
# Replace or extend this with your 100 product names if desired.
PRODUCT_NAMES: List[str] = [
    "Hydraulic Power Unit",
    "High-Pressure Compressor",
]

# Domains strongly associated with stock photos or watermarks.
STOCK_DOMAINS = {
    "shutterstock.com",
    "alamy.com",
    "gettyimages.com",
    "istockphoto.com",
    "dreamstime.com",
    "depositphotos.com",
    "123rf.com",
    "adobe.com",
    "vectorstock.com",
    "freepik.com",
    "bigstockphoto.com",
    "envato.com",
    "canstockphoto.com",
}

# Words that often indicate watermarks, logos, or non‑product imagery.
WATERMARK_HINTS = {
    "watermark",
    "stock photo",
    "stockphoto",
    "gettyimages",
    "shutterstock",
    "istock",
    "logo",
    "icon",
    "mockup",
    "sample text",
}

HUMAN_OR_CLOTHING_HINTS = {
    "portrait",
    "people",
    "person",
    "man",
    "woman",
    "worker",
    "engineer",
    "team",
    "group",
    "model",
    "fashion",
    "shirt",
    "jacket",
    "helmet selfie",
}


# ---------------------------------------------------------------------------
# Utility functions
# ---------------------------------------------------------------------------

def slugify(text: str) -> str:
    text = text.strip().lower()
    text = re.sub(r"[^a-z0-9]+", "-", text)
    text = re.sub(r"-{2,}", "-", text)
    return text.strip("-") or "product"


def read_products_from_csv(path: Path, name_column: str) -> List[str]:
    products: List[str] = []
    with path.open("r", newline="", encoding="utf-8-sig") as f:
        reader = csv.DictReader(f)
        if name_column not in reader.fieldnames:
            raise ValueError(
                f"CSV column '{name_column}' not found. "
                f"Available columns: {reader.fieldnames}"
            )
        for row in reader:
            name = (row.get(name_column) or "").strip()
            if name:
                products.append(name)
    return products


def build_query(product_name: str) -> str:
    # Keyword‑reinforced query as requested
    return (
        f"{product_name} industrial equipment hydraulic mechanical OEM "
        f"factory machine high resolution photo"
    )


def get_domain(url: str) -> str:
    try:
        parsed = urlparse(url)
        return (parsed.netloc or "").lower()
    except Exception:
        return ""


def text_has_any(text: str, keywords: Iterable[str]) -> bool:
    t = text.lower()
    return any(k in t for k in keywords)


def is_likely_watermarked(url: str, title: str, source: str) -> bool:
    domain = get_domain(url)
    if any(d in domain for d in STOCK_DOMAINS):
        return True
    combined = f"{url} {title} {source}".lower()
    return text_has_any(combined, WATERMARK_HINTS)


def is_likely_human_or_logo(url: str, title: str, source: str) -> bool:
    combined = f"{url} {title} {source}".lower()
    return text_has_any(combined, HUMAN_OR_CLOTHING_HINTS) or "logo" in combined


def is_relevant_equipment_image(meta: Dict) -> bool:
    """
    Best-effort relevance filter based only on metadata.

    Excludes:
    - obvious stock/watermarked domains
    - images likely containing people, clothing, or logos
    """
    url = meta.get("url") or ""
    title = meta.get("title") or ""
    source = meta.get("source") or ""

    if not url:
        return False

    if is_likely_watermarked(url, title, source):
        return False

    if is_likely_human_or_logo(url, title, source):
        return False

    # Prefer URLs that look like real images
    if not re.search(r"\.(jpg|jpeg|png|webp|bmp|tiff)(\?|$)", url.lower()):
        # Still allow if content-type later confirms it's an image, but de-prioritize
        pass

    # Optionally, require some equipment-related terms in title/source.
    equipment_keywords = [
        "machine",
        "equipment",
        "pump",
        "compressor",
        "valve",
        "hydraulic",
        "mechanical",
        "industrial",
        "drilling",
        "pipeline",
        "unit",
    ]
    text = f"{title} {source}".lower()
    if not text_has_any(text, equipment_keywords):
        # Not a hard reject, but mark as low confidence.
        meta["_low_confidence"] = True
    return True


# ---------------------------------------------------------------------------
# API clients
# ---------------------------------------------------------------------------


def search_images_serpapi(
    query: str,
    api_key: str,
    page: int = 0,
    num_results: int = 50,
) -> List[Dict]:
    """
    Uses SerpAPI Google Images.

    Docs: https://serpapi.com/images-results
    """
    url = "https://serpapi.com/search.json"
    params = {
        "engine": "google_images",
        "q": query,
        "api_key": api_key,
        "ijn": page,            # page index
        "num": num_results,     # may not always be honored, but SerpAPI supports it
    }
    resp = requests.get(url, params=params, timeout=30)
    resp.raise_for_status()
    data = resp.json()
    images = data.get("images_results") or []
    results: List[Dict] = []
    for img in images:
        image_url = (
            img.get("original")
            or img.get("image")
            or img.get("thumbnail")
            or ""
        )
        if not image_url:
            continue
        results.append(
            {
                "url": image_url,
                "title": img.get("title") or "",
                "source": img.get("source") or "",
            }
        )
    return results


def search_images_bing(
    query: str,
    api_key: str,
    endpoint: str,
    offset: int = 0,
    count: int = 50,
) -> List[Dict]:
    """
    Uses Bing Image Search API.

    Docs: https://learn.microsoft.com/azure/cognitive-services/bing-image-search/
    """
    if endpoint.endswith("/"):
        endpoint = endpoint[:-1]
    url = f"{endpoint}/bing/v7.0/images/search"
    headers = {"Ocp-Apim-Subscription-Key": api_key}
    params = {
        "q": query,
        "offset": offset,
        "count": count,
        "imageType": "Photo",
        "safeSearch": "Strict",
    }
    resp = requests.get(url, headers=headers, params=params, timeout=30)
    resp.raise_for_status()
    data = resp.json()
    images = data.get("value") or []
    results: List[Dict] = []
    for img in images:
        image_url = img.get("contentUrl") or img.get("thumbnailUrl") or ""
        if not image_url:
            continue
        results.append(
            {
                "url": image_url,
                "title": img.get("name") or "",
                "source": img.get("hostPageDomainFriendlyName") or "",
            }
        )
    return results


# ---------------------------------------------------------------------------
# Download logic
# ---------------------------------------------------------------------------


def ensure_dir(path: Path) -> None:
    path.mkdir(parents=True, exist_ok=True)


def infer_extension_from_content_type(content_type: str) -> str:
    if not content_type:
        return "jpg"
    content_type = content_type.lower()
    if "png" in content_type:
        return "png"
    if "webp" in content_type:
        return "webp"
    if "bmp" in content_type:
        return "bmp"
    if "gif" in content_type:
        return "gif"
    return "jpg"


def download_image(url: str, dest: Path, timeout: int = 30) -> bool:
    try:
        resp = requests.get(url, timeout=timeout, stream=True)
        if resp.status_code != 200:
            logging.warning("HTTP %s for %s", resp.status_code, url)
            return False

        content_type = resp.headers.get("Content-Type", "")
        if "image" not in content_type.lower():
            logging.warning("Non-image content-type '%s' for %s", content_type, url)
            return False

        ext = infer_extension_from_content_type(content_type)
        dest = dest.with_suffix(f".{ext}")
        with dest.open("wb") as f:
            for chunk in resp.iter_content(8192):
                if chunk:
                    f.write(chunk)
        return True
    except Exception as e:
        logging.warning("Failed to download %s: %s", url, e)
        return False


# ---------------------------------------------------------------------------
# Main per-product workflow
# ---------------------------------------------------------------------------


def fetch_and_save_images_for_product(
    product_name: str,
    output_root: Path,
    provider: str,
    api_key: str,
    max_per_product: int,
    retries: int,
    serpapi_num_results: int,
    bing_endpoint: Optional[str],
    delay: float,
) -> Tuple[int, str]:
    """
    Returns (downloaded_count, status).
    status in {"ok", "no_results", "api_error"}.
    """
    query = build_query(product_name)
    slug = slugify(product_name)
    product_dir = output_root / slug
    ensure_dir(product_dir)

    downloaded = 0
    page = 0
    status = "no_results"

    for attempt in range(retries + 1):
        try:
            if provider == "serpapi":
                logging.info(
                    "Searching (SerpAPI) for '%s' [page %d]...", product_name, page
                )
                candidates = search_images_serpapi(
                    query=query, api_key=api_key, page=page, num_results=serpapi_num_results
                )
            else:
                assert bing_endpoint is not None
                offset = page * serpapi_num_results
                logging.info(
                    "Searching (Bing) for '%s' [offset %d]...",
                    product_name,
                    offset,
                )
                candidates = search_images_bing(
                    query=query,
                    api_key=api_key,
                    endpoint=bing_endpoint,
                    offset=offset,
                    count=serpapi_num_results,
                )
        except Exception as e:
            logging.error("API error for '%s': %s", product_name, e)
            status = "api_error"
            time.sleep(delay)
            page += 1
            continue

        if not candidates:
            logging.info("No candidates for '%s' on attempt %d", product_name, attempt)
            page += 1
            time.sleep(delay)
            continue

        # Filter candidates by relevance
        good_candidates: List[Dict] = []
        low_confidence: List[Dict] = []

        for meta in candidates:
            if not is_relevant_equipment_image(meta):
                continue
            if meta.get("_low_confidence"):
                low_confidence.append(meta)
            else:
                good_candidates.append(meta)

        # Prefer strong candidates; if not enough, top up with low-confidence ones
        ordered = good_candidates + low_confidence

        for meta in ordered:
            if downloaded >= max_per_product:
                break
            url = meta["url"]
            safe_idx = downloaded + 1
            filename = f"{slug}-{safe_idx}"
            dest = product_dir / filename

            if download_image(url, dest):
                logging.info("Saved %s for '%s' → %s", url, product_name, dest)
                downloaded += 1
                status = "ok"

        if downloaded >= max_per_product:
            break

        # Otherwise, try next page / offset
        page += 1
        time.sleep(delay)

    if downloaded == 0 and status == "no_results":
        logging.warning("No suitable images found for '%s'", product_name)

    return downloaded, status


# ---------------------------------------------------------------------------
# CLI / main
# ---------------------------------------------------------------------------


def parse_args(argv: Optional[List[str]] = None) -> argparse.Namespace:
    p = argparse.ArgumentParser(
        description="Scrape 1–3 industrial equipment photos per product."
    )
    p.add_argument(
        "--provider",
        choices=["serpapi", "bing"],
        default="serpapi",
        help="Image search provider (default: serpapi).",
    )
    p.add_argument(
        "--api-key",
        help=(
            "API key. If omitted, uses SERPAPI_KEY or BING_API_KEY "
            "environment variable depending on provider."
        ),
    )
    p.add_argument(
        "--bing-endpoint",
        help="Bing endpoint base URL (e.g. https://api.bing.microsoft.com). Required if provider=bing.",
    )
    p.add_argument(
        "--csv",
        type=Path,
        help="Path to CSV with product names.",
    )
    p.add_argument(
        "--name-column",
        default="name",
        help="CSV column name containing product names (default: name).",
    )
    p.add_argument(
        "--output-dir",
        type=Path,
        default=Path("images"),
        help="Root output directory (default: ./images).",
    )
    p.add_argument(
        "--max-per-product",
        type=int,
        default=3,
        help="Maximum images per product (default: 3).",
    )
    p.add_argument(
        "--retries",
        type=int,
        default=2,
        help="Number of extra result pages to try if nothing relevant is found (default: 2).",
    )
    p.add_argument(
        "--per-page",
        type=int,
        default=30,
        help="Number of results to request per page (default: 30).",
    )
    p.add_argument(
        "--delay",
        type=float,
        default=1.0,
        help="Seconds to sleep between API calls (default: 1.0).",
    )
    p.add_argument(
        "--log-file",
        type=Path,
        default=Path("scrape_log.csv"),
        help="CSV file to log results (default: scrape_log.csv).",
    )
    p.add_argument(
        "--limit",
        type=int,
        default=0,
        help="Limit to first N products (0 = no limit, default: 0).",
    )
    return p.parse_args(argv)


def main(argv: Optional[List[str]] = None) -> None:
    args = parse_args(argv)

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(message)s",
    )

    # Resolve API key
    api_key = args.api_key
    if not api_key:
        env_var = "SERPAPI_KEY" if args.provider == "serpapi" else "BING_API_KEY"
        api_key = os.environ.get(env_var)
    if not api_key:
        logging.error(
            "API key is required. Provide --api-key or set %s environment variable.",
            "SERPAPI_KEY" if args.provider == "serpapi" else "BING_API_KEY",
        )
        sys.exit(1)

    if args.provider == "bing" and not args.bing_endpoint:
        logging.error("--bing-endpoint is required when provider=bing.")
        sys.exit(1)

    # Load product names
    if args.csv:
        logging.info("Reading products from CSV: %s", args.csv)
        products = read_products_from_csv(args.csv, args.name_column)
    else:
        logging.info("Using built-in PRODUCT_NAMES list.")
        products = [p for p in PRODUCT_NAMES if p.strip()]

    if not products:
        logging.error("No products found.")
        sys.exit(1)

    # Apply limit if specified
    if args.limit > 0:
        products = products[:args.limit]
        logging.info("Limited to first %d products.", args.limit)

    logging.info("Loaded %d products.", len(products))
    ensure_dir(args.output_dir)

    # Prepare log
    log_rows: List[Tuple[str, int, str, str]] = []
    # Columns: product_name,downloaded_count,status,provider

    for idx, product_name in enumerate(products, start=1):
        logging.info("=== [%d/%d] %s ===", idx, len(products), product_name)
        downloaded, status = fetch_and_save_images_for_product(
            product_name=product_name,
            output_root=args.output_dir,
            provider=args.provider,
            api_key=api_key,
            max_per_product=args.max_per_product,
            retries=args.retries,
            serpapi_num_results=args.per_page,
            bing_endpoint=args.bing_endpoint,
            delay=args.delay,
        )
        log_rows.append((product_name, downloaded, status, args.provider))

    # Write log CSV
    with args.log_file.open("w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(["product_name", "downloaded_count", "status", "provider"])
        writer.writerows(log_rows)

    logging.info("Done. Results logged to %s", args.log_file)


if __name__ == "__main__":
    main()
