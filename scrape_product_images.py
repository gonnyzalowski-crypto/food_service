#!/usr/bin/env python3
"""
Script to scrape product images using SerpAPI and save them to the web/images folder.
"""

import os
import requests
import json
import time
from pathlib import Path

# SerpAPI key
SERPAPI_KEY = "fd1af478a1e016f08f0b6c402a2324aaeee5cec1f0caa46a7eb7e67717a08218"

# Products to scrape images for
PRODUCTS = [
    {"name": "Decanting Centrifuge 14 inch oilfield", "slug": "decanting-centrifuge-14"},
    {"name": "Drill Pipe Elevator 500 Ton oilfield", "slug": "drill-pipe-elevator-500-ton"},
    {"name": "Pipeline Pig Launcher 48 inch", "slug": "pipeline-pig-launcher-48"},
    {"name": "Pipeline Pig Receiver 48 inch", "slug": "pipeline-pig-receiver-48"},
    {"name": "Power Tong 150K ft-lb oilfield drilling", "slug": "power-tong-150k-ft-lb"},
    {"name": "Mud Mixing System drilling rig", "slug": "mud-mixing-system-complete"},
    {"name": "Shale Shaker 4 Panel drilling", "slug": "shale-shaker-4-panel"},
    {"name": "Vacuum Degasser drilling mud", "slug": "vacuum-degasser-1500-gpm"},
    {"name": "Rotary Slips 500 Ton drilling", "slug": "rotary-slips-500-ton"},
    {"name": "Gate Valve 24 inch Class 900 pipeline", "slug": "gate-valve-24-class-900"},
    {"name": "Pipeline Repair Clamp 48 inch", "slug": "pipeline-repair-clamp-48"},
    {"name": "Hydrocyclone Desander 12 inch drilling", "slug": "hydrocyclone-desander-12"},
    {"name": "Swing Check Valve 20 inch pipeline", "slug": "swing-check-valve-20"},
]

# Base path for images
IMAGES_DIR = Path(__file__).parent / "web" / "images"

def search_images(query: str, num_images: int = 3) -> list:
    """Search for images using SerpAPI Google Images."""
    url = "https://serpapi.com/search.json"
    params = {
        "engine": "google_images",
        "q": query,
        "api_key": SERPAPI_KEY,
        "num": num_images,
        "safe": "active",
        "tbm": "isch",
    }
    
    try:
        response = requests.get(url, params=params, timeout=30)
        response.raise_for_status()
        data = response.json()
        
        images = []
        if "images_results" in data:
            for img in data["images_results"][:num_images]:
                if "original" in img:
                    images.append(img["original"])
        return images
    except Exception as e:
        print(f"Error searching for '{query}': {e}")
        return []

def download_image(url: str, save_path: Path) -> bool:
    """Download an image from URL and save it."""
    try:
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
        }
        response = requests.get(url, headers=headers, timeout=30, stream=True)
        response.raise_for_status()
        
        # Determine extension from content-type
        content_type = response.headers.get("content-type", "")
        if "jpeg" in content_type or "jpg" in content_type:
            ext = ".jpg"
        elif "png" in content_type:
            ext = ".png"
        elif "webp" in content_type:
            ext = ".webp"
        else:
            ext = ".jpg"  # Default
        
        # Update save path with correct extension
        save_path = save_path.with_suffix(ext)
        
        with open(save_path, "wb") as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        
        print(f"  ✓ Downloaded: {save_path.name}")
        return True
    except Exception as e:
        print(f"  ✗ Failed to download: {e}")
        return False

def main():
    print("=" * 60)
    print("Product Image Scraper")
    print("=" * 60)
    
    for product in PRODUCTS:
        name = product["name"]
        slug = product["slug"]
        
        print(f"\n[{slug}] Searching for: {name}")
        
        # Create directory for product images
        product_dir = IMAGES_DIR / slug
        product_dir.mkdir(parents=True, exist_ok=True)
        
        # Remove existing images
        for existing in product_dir.glob("*"):
            existing.unlink()
            print(f"  Removed: {existing.name}")
        
        # Search for images
        image_urls = search_images(name, num_images=3)
        
        if not image_urls:
            print(f"  ✗ No images found")
            continue
        
        # Download images
        for i, url in enumerate(image_urls, 1):
            save_path = product_dir / f"{i}"
            download_image(url, save_path)
            time.sleep(0.5)  # Rate limiting
        
        time.sleep(1)  # Rate limiting between products
    
    print("\n" + "=" * 60)
    print("Done!")
    print("=" * 60)

if __name__ == "__main__":
    main()
