<?php

namespace App\Services;

class QrisConverter
{
    public static function makeDynamic(string $staticQris, int $amount): string
    {
        // 1. Hapus 4 karakter checksum CRC16 lama di akhir
        $qrisWithoutCrc = substr(trim($staticQris), 0, -4);

        // 2. Ubah indikator dari Statis (010211) ke Dinamis (010212)
        $step1 = str_replace('010211', '010212', $qrisWithoutCrc);

        // 3. Pecah berdasarkan kode negara (5802ID) untuk menyisipkan tag nominal (54)
        $parts = explode('5802ID', $step1);

        if (count($parts) !== 2) {
            throw new \Exception('Format string QRIS tidak valid.');
        }

        // 4. Format Tag 54: "54" + panjang digit (2 angka) + nominal
        $amountStr = (string) $amount;
        $amountLength = str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT);
        $amountTag = '54' . $amountLength . $amountStr;

        // 5. Gabungkan kembali dan hitung CRC16 baru
        $newQris = $parts[0] . $amountTag . '5802ID' . $parts[1];

        return $newQris . self::calculateCrc16($newQris);
    }

    private static function calculateCrc16(string $str): string
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);

        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord($str[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}