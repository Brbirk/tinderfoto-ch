#!/bin/bash
# Script to download all images for tinderfoto.ch website
# Run this script from the TINDERFOTO-WEBSITE directory

mkdir -p images

echo "Downloading images..."

# Logo
curl -sL -o images/logo-tinderfoto.png "https://tinderfoto.ch/wp-content/uploads/2020/04/logo-tinderfoto.png"
echo "✓ Logo downloaded"

# Hero / Homepage images
curl -sL -o images/partnersuche5-head-2-scaled.jpg "https://tinderfoto.ch/wp-content/uploads/2021/10/partnersuche5-head-2-scaled.jpg"
echo "✓ Hero image downloaded"

curl -sL -o images/partnersuche5-scaled.jpg "https://tinderfoto.ch/wp-content/uploads/2021/10/partnersuche5-scaled.jpg"
echo "✓ Partnersuche5 downloaded"

# Team photos
curl -sL -o images/bruno-birkhofer-portraitfotograf.jpg "https://tinderfoto.ch/wp-content/uploads/2022/01/bruno-birkhofer-portraitfotograf.jpg"
echo "✓ Bruno photo downloaded"

curl -sL -o images/andrea-c.jpg "https://tinderfoto.ch/wp-content/uploads/2024/01/andrea-c.jpg"
echo "✓ Andrea photo downloaded"

curl -sL -o images/felix-peter-portraitfotograf.jpg "https://tinderfoto.ch/wp-content/uploads/2022/01/felix-peter-portraitfotograf.jpg"
echo "✓ Felix photo downloaded"

# Blog post featured images
curl -sL -o images/partnersuche3-1080x675.jpg "https://tinderfoto.ch/wp-content/uploads/2021/10/partnersuche3-1080x675.jpg"
echo "✓ Blog image 1 downloaded"

curl -sL -o images/partnersuche5-head-2-1080x675.jpg "https://tinderfoto.ch/wp-content/uploads/2021/10/partnersuche5-head-2-1080x675.jpg"
echo "✓ Blog image 2 downloaded"

curl -sL -o images/Begleitperson-beim-Tinder-Foto-Shooting.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Begleitperson-beim-Tinder-Foto-Shooting-Warum-es-eine-gute-Idee-ist-1080x675.jpg"
echo "✓ Blog image 3 downloaded"

curl -sL -o images/Ein-3-Tage-Bart-beim-Foto-Shooting.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Ein-3-Tage-Bart-beim-Foto-Shooting-1080x675.jpg"
echo "✓ Blog image 4 downloaded"

curl -sL -o images/Fotografie-und-die-Schweizer-Dating-Industrie.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Fotografie-und-die-Schweizer-Dating-Industrie-1080x675.jpg"
echo "✓ Blog image 5 downloaded"

curl -sL -o images/Liebe-kann-man-nicht-planen.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Liebe-kann-man-nicht-planen-%E2%80%93-oder-doch-1080x675.jpg"
echo "✓ Blog image 6 downloaded"

curl -sL -o images/Aphrodisierende-Lebensmittel.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Aphrodisierende-Lebensmittel.jpg"
echo "✓ Blog image 7 downloaded"

curl -sL -o images/Online-Dating-in-der-Krise.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Online-Dating-in-der-Krise-1080x675.jpg"
echo "✓ Blog image 8 downloaded"

curl -sL -o images/kostenlosen-Singleboersen.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/kostenlosen-Singleboersen-1080x675.jpg"
echo "✓ Blog image 9 downloaded"

curl -sL -o images/Strategien-fuer-mehr-Erfolg-beim-Online-Dating.jpg "https://tinderfoto.ch/wp-content/uploads/2025/01/Strategien-fuer-mehr-Erfolg-beim-Online-Dating-1080x675.jpg"
echo "✓ Blog image 10 downloaded"

echo ""
echo "All images downloaded! Total files:"
ls -la images/ | wc -l
echo ""
echo "To switch from remote URLs to local images, run:"
echo "  sed -i 's|https://tinderfoto.ch/wp-content/uploads/2020/04/logo-tinderfoto.png|images/logo-tinderfoto.png|g' *.html blog/*.html"
echo "  (and similar for all other image URLs)"
