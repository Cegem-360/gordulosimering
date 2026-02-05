#!/usr/bin/env python3
"""
Brand Logo Scraper
==================

This script scrapes brand logos from official websites and CDN sources.

## Working Solutions

### 1. WorldVectorLogo CDN (Most Reliable)
   URL Pattern: https://cdn.worldvectorlogo.com/logos/{brand-name}.svg

   Working examples:
   - SKF: https://cdn.worldvectorlogo.com/logos/skf-logo.svg
   - FAG: https://cdn.worldvectorlogo.com/logos/fag.svg
   - INA: https://cdn.worldvectorlogo.com/logos/ina.svg
   - BAHCO: https://cdn.worldvectorlogo.com/logos/bahco.svg
   - KS-TOOLS: https://cdn.worldvectorlogo.com/logos/ks-tools.svg

   Note: Some logos return 403 (Beta, ZVL)

### 2. Logotyp.us CDN
   URL Pattern: https://logotyp.us/file/{brand-name}.svg

   Working examples:
   - Duracell: https://logotyp.us/file/duracell.svg (returns SVG)

   Note: Many brands return HTML error pages (not found pages)

### 3. Direct Website Scraping with BeautifulSoup
   Best approach for brands with accessible websites.

   Working examples:
   - Duracell UK: https://www.duracell.co.uk/ -> Found header logo
   - Fabory: https://www.fabory.com/ -> Found nav logo
   - Energizer: Direct PNG link worked
   - OKS Germany: Direct PNG link worked
   - Tente: Direct PNG link worked
   - Norma Group: Direct SVG link worked
   - Seeger Orbis: Direct SVG link worked
   - ZKL: Direct SVG link worked

## Non-Working Solutions / Issues

### 1. Wikipedia/Wikimedia Commons
   - Direct SVG links return "File not found" error pages
   - Need to use thumbnail URLs instead, but these return 403

### 2. SeekLogo.com
   - Returns HTML error pages instead of images
   - Requires authentication/cookies for direct downloads

### 3. Schaeffler.de (FAG/INA)
   - Returns 404 for most logo URLs
   - Website structure has changed

### 4. Sites with SSL Issues
   - betatools.com: SSL certificate mismatch
   - Some .hu domains have certificate issues

### 5. Sites Blocking Scrapers
   - bahco.com: Returns 403 Forbidden
   - beta-tools.com: Returns 403 Forbidden

## Successful Logos Downloaded

| Brand      | Source                                    | Format |
|------------|-------------------------------------------|--------|
| SKF        | worldvectorlogo.com                       | SVG    |
| LOCTITE    | henkel-dam.com                            | PNG    |
| ZKL        | zkl.cz                                    | SVG    |
| FABORY     | fabory.com                                | SVG    |
| DURACELL   | duracell.co.uk                            | SVG    |
| ENERGIZER  | energizer.com                             | PNG    |
| OKS        | oks-germany.com                           | PNG    |
| NORMA      | normagroup.com                            | SVG    |
| TENTE      | cdn.tente.com                             | PNG    |
| SEEGER     | seeger-orbis.de                           | SVG    |
| BAHCO      | worldvectorlogo.com                       | SVG    |
| KS-TOOLS   | worldvectorlogo.com                       | SVG    |
| TIMKEN     | timken.com                                | PNG    |
| FAG        | worldvectorlogo.com                       | SVG    |
| INA        | worldvectorlogo.com                       | SVG    |

## Missing Logos (Need Manual Download)

| Brand | Reason |
|-------|--------|
| NICRO | Website returns 404/redirects, no CDN source found |
| BETA  | SSL certificate issues, CDN returns 403 |
| ZVL   | CDN returns 403, DNS resolution issues for main site |

## Tips for Future Scraping

1. Always use a proper User-Agent header
2. Check Content-Type header to verify you got an image, not HTML
3. worldvectorlogo.com is the most reliable CDN source
4. For direct website scraping, look for:
   - header img[src*="logo"]
   - nav img[src*="logo"]
   - .logo img
   - img[class*="logo"]
5. Some sites require cookies/session to download assets
6. Consider using Puppeteer/Playwright for JavaScript-rendered pages

## Usage

    python3 scrape_logos.py

"""

import requests
from bs4 import BeautifulSoup
import os
from urllib.parse import urljoin, urlparse

# Directory to save logos
LOGOS_DIR = '/Users/attila/Herd/gordulosimering/resources/images/brands'
os.makedirs(LOGOS_DIR, exist_ok=True)

headers = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.9',
}


def download_file(url, filename):
    """Download a file from URL"""
    try:
        response = requests.get(url, headers=headers, timeout=30, allow_redirects=True)
        if response.status_code == 200:
            content_type = response.headers.get('content-type', '')
            if 'image' in content_type or 'svg' in content_type or url.endswith(('.svg', '.png', '.jpg', '.jpeg', '.webp')):
                filepath = os.path.join(LOGOS_DIR, filename)
                with open(filepath, 'wb') as f:
                    f.write(response.content)
                print(f"  Downloaded: {filename} ({len(response.content)} bytes)")
                return True
            else:
                print(f"  Not an image: {content_type}")
        else:
            print(f"  HTTP {response.status_code}")
    except Exception as e:
        print(f"  Error: {e}")
    return False


def find_logo_in_page(url, brand_name):
    """Find logo URL in a webpage"""
    try:
        response = requests.get(url, headers=headers, timeout=30, allow_redirects=True)
        if response.status_code != 200:
            print(f"  Failed to fetch page: HTTP {response.status_code}")
            return None

        soup = BeautifulSoup(response.text, 'html.parser')

        # Look for logo in common patterns
        logo_patterns = [
            # Header/navbar logos
            ('header img[src*="logo"]', 'src'),
            ('header img[alt*="logo"]', 'src'),
            ('nav img[src*="logo"]', 'src'),
            ('nav img[alt*="logo"]', 'src'),
            ('.logo img', 'src'),
            ('#logo img', 'src'),
            ('a.logo img', 'src'),
            ('img.logo', 'src'),
            ('img[class*="logo"]', 'src'),
            ('img[id*="logo"]', 'src'),
            # SVG logos
            ('header svg', None),
            ('nav svg', None),
            ('.logo svg', None),
            # Link with logo
            ('a[href="/"] img', 'src'),
            ('a[class*="home"] img', 'src'),
            # Picture elements
            ('picture source[srcset*="logo"]', 'srcset'),
        ]

        for selector, attr in logo_patterns:
            elements = soup.select(selector)
            for elem in elements:
                if attr:
                    logo_url = elem.get(attr)
                    if logo_url:
                        # Handle srcset
                        if ',' in logo_url:
                            logo_url = logo_url.split(',')[0].strip().split()[0]
                        full_url = urljoin(url, logo_url)
                        print(f"  Found: {selector} -> {full_url[:80]}...")
                        return full_url
                else:
                    # For SVG elements, try to find them inline or as a link
                    use_elem = elem.find('use')
                    if use_elem and use_elem.get('href'):
                        href = use_elem.get('href') or use_elem.get('xlink:href')
                        if href:
                            return urljoin(url, href)

        # Try to find any image with brand name in src or alt
        for img in soup.find_all('img'):
            src = img.get('src', '')
            alt = img.get('alt', '')
            if brand_name.lower() in src.lower() or brand_name.lower() in alt.lower():
                full_url = urljoin(url, src)
                print(f"  Found by brand name: {full_url[:80]}...")
                return full_url

    except Exception as e:
        print(f"  Error fetching page: {e}")
    return None


# Direct logo URLs that are known to work
direct_logos = [
    ('skf', 'https://cdn.worldvectorlogo.com/logos/skf-logo.svg'),
    ('fag', 'https://cdn.worldvectorlogo.com/logos/fag.svg'),
    ('ina', 'https://cdn.worldvectorlogo.com/logos/ina.svg'),
    ('bahco', 'https://cdn.worldvectorlogo.com/logos/bahco.svg'),
    ('ks-tools', 'https://cdn.worldvectorlogo.com/logos/ks-tools.svg'),
    ('loctite', 'https://dm.henkel-dam.com/is/image/henkel/Locite-logo'),
    ('energizer', 'https://energizer.com/eu/hungary/wp-content/uploads/sites/30/2024/04/Energizer_brand.png'),
    ('oks', 'https://www.oks-germany.com/ecomaXL/media/oks-logo.png'),
    ('tente', 'https://cdn.tente.com/media/76/38/28/1700315047/tente-logo-long_%281%29.png'),
    ('norma', 'https://www.normagroup.com/content/experience-fragments/normagroup-com/global/en/site/header/master/_jcr_content/root/container/header/logo.coreimg.svg/1728631485113/mainlogo.svg'),
    ('seeger', 'https://www.seeger-orbis.de/fileadmin/assets/logo-dark.svg'),
    ('timken', 'https://www.timken.com/wp-content/uploads/2016/07/Timken_logo.png'),
    ('zkl', 'https://www.zkl.cz/ZKL/media/system/img/logo.svg'),
]

# Brands to scrape from websites
brands_to_scrape = [
    ('duracell', 'https://www.duracell.co.uk/'),
    ('fabory', 'https://www.fabory.com/'),
]


if __name__ == '__main__':
    print("=" * 60)
    print("Brand Logo Scraper")
    print("=" * 60)

    print("\n1. Downloading from direct CDN URLs...")
    print("-" * 40)

    for brand_name, url in direct_logos:
        print(f"\n{brand_name.upper()}:")
        print(f"  URL: {url}")

        # Determine file extension
        parsed = urlparse(url)
        path = parsed.path.lower()
        if '.svg' in path:
            ext = '.svg'
        elif '.png' in path:
            ext = '.png'
        elif '.jpg' in path or '.jpeg' in path:
            ext = '.jpg'
        else:
            ext = '.svg'

        filename = f"{brand_name}{ext}"
        download_file(url, filename)

    print("\n\n2. Scraping logos from websites...")
    print("-" * 40)

    for brand_name, url in brands_to_scrape:
        print(f"\n{brand_name.upper()}:")
        print(f"  URL: {url}")

        logo_url = find_logo_in_page(url, brand_name)
        if logo_url:
            parsed = urlparse(logo_url)
            path = parsed.path.lower()
            if '.svg' in path:
                ext = '.svg'
            elif '.png' in path:
                ext = '.png'
            elif '.jpg' in path or '.jpeg' in path:
                ext = '.jpg'
            elif '.webp' in path:
                ext = '.webp'
            else:
                ext = '.png'

            filename = f"{brand_name}{ext}"
            download_file(logo_url, filename)
        else:
            print("  No logo found")

    print("\n" + "=" * 60)
    print("Done! Check:", LOGOS_DIR)
    print("=" * 60)
