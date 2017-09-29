<?php
namespace Concrete\Package\VatcodeCodicefiscale;

class Checker
{
    /**
     * Value type: VAT code.
     *
     * @var int
     */
    const TYPE_VATCODE = 1;

    /**
     * Value type: codice fiscale.
     *
     * @var int
     */
    const TYPE_CODICEFISCALE = 2;
    private static $MAP_CODICEFISCALE = [
        '0' => 1, '1' => 0, '2' => 5, '3' => 7, '4' => 9, '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        'A' => 1, 'B' => 0, 'C' => 5, 'D' => 7, 'E' => 9, 'F' => 13, 'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21,
        'K' => 2, 'L' => 4, 'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3, 'Q' => 6, 'R' => 8, 'S' => 12, 'T' => 14,
        'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23,
    ];

    /**
     * Return the normalized form of a value (strip out white spaces, make uppercase).
     *
     * @param string|mixed $value
     *
     * @return string
     */
    public function normalize($value)
    {
        if (is_string($value)) {
            $result = preg_replace('/\s+/', '', $value);
            if (preg_match('/^[\x20-\x7f]+$/', $result)) {
                $result = strtoupper($result);
            }
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Get the type of a value.
     *
     * @param string $value The value to be checked (it should be normalized with Checker::normalize)
     *
     * @return int|null
     */
    public function getType($value)
    {
        if ($this->isVatCode($value)) {
            $result = static::TYPE_VATCODE;
        } elseif ($this->isCodiceFiscale($value)) {
            $result = static::TYPE_CODICEFISCALE;
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Check if a value contains a valid VAT code string.
     *
     * @param string $value The value to be checked (it should be normalized with Checker::normalize)
     *
     * @return bool
     */
    public function isVatCode($value)
    {
        $result = false;
        if (is_string($value) && preg_match('/^[0-9]{11}$/', $value)) {
            $sum = 0;
            for($i = 0; $i <= 9; $i += 2) {
                $sum += (int) $value[$i];
            }
            for($i = 1; $i <= 9; $i += 2) {
                $c = 2 * (int) $value[$i];
                if($c > 9) {
                    $c -= 9;
                }
                $sum += $c;
            }
            $checkCode = (10 - ($sum % 10)) % 10;
            if ($checkCode === (int) $value[10]) {
                return true;
            }
        }

        return $result;
    }

    /**
     * Check if a value contains a valid codice fiscale string.
     *
     * @param string $value The value to be checked (it should be normalized with Checker::normalize)
     *
     * @return bool
     */
    public function isCodiceFiscale($value)
    {
        $result = false;
        if (is_string($value) && preg_match('/^[A-Z]{6}[A-Z0-9]{2}[A-Z]{1}[A-Z0-9]{2}[A-Z]{1}[A-Z0-9]{3}[A-Z]{1}$/', $value)) {
            $sum = 0;
            for($i = 1; $i <= 13; $i += 2) {
                $char = $value[$i];
                if($char >= '0' && $char <= '9') {
                    $sum += (int) $char;
                }
                else {
                    $sum += ord($char) - ord('A');
                }
            }
            for($i = 0; $i <= 14; $i += 2) {
                $char = $value[$i];
                if (!isset(static::$MAP_CODICEFISCALE[$char])) {
                    $sum = null;
                    break;
                }
                else {
                    $sum += static::$MAP_CODICEFISCALE[$char];
                }
            }
            if($sum !== null) {
                $checkChar = chr($sum % 26 + ord('A'));
                if ($value[15] === $checkChar) {
                    $result = true;
                }
            }
        }

        return $result;
    }
}
