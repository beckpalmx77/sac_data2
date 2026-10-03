<?php

function getGps($exifCoord, $hemi) {
    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

    $flip = ($hemi == 'S' || $hemi == 'W') ? -1 : 1;

    return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
}

function gps2Num($coordPart) {
    $parts = explode('/', $coordPart);
    if (count($parts) <= 0) return 0;
    if (count($parts) == 1) return $parts[0];
    return floatval($parts[0]) / floatval($parts[1]);
}

function getGpsCoordinates($file) {
    $exif = exif_read_data($file, 0, true);
    if ($exif && isset($exif['GPS'])) {
        $lat = getGps($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
        $lon = getGps($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
        return array('latitude' => $lat, 'longitude' => $lon);
    } else {
        return null;
    }
}

// ใช้ฟังก์ชันเพื่อดึงข้อมูล GPS
$file = 'singha.jpg';
$gps = getGpsCoordinates($file);

if ($gps) {
    echo "Latitude: " . $gps['latitude'] . "\n";
    echo "Longitude: " . $gps['longitude'] . "\n";
} else {
    echo "ไม่พบข้อมูล GPS ในรูปภาพนี้";
}

