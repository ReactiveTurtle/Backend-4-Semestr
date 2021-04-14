<?php


class MathExtensions
{
    public static function clamp($value, $min, $max)
    {
        return min($max, max($value, $min));
    }
}