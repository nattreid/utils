<?php

declare(strict_types=1);

namespace NAttreid\Utils;

final class Strings extends \Nette\Utils\Strings
{

	public static function getKeyWords(string $text, int $maxLen = 60): string
	{
		$keyWords = [];
		preg_match_all('/<a[^>]*>[^<]+<\/a>/i', $text, $found);
		if (isset($found[0])) {
			foreach ($found[0] as $kw) {
				$keyWords[] = self::lower(self::trim(strip_tags($kw)));
			}
		}

		preg_match_all('/<(h2|strong)[^>]*>[^<]+<\/(h2|strong)>/i', $text, $found);
		if (isset($found[0])) {
			foreach ($found[0] as $kw) {
				$keyWords[] = self::lower(self::trim(strip_tags($kw)));
			}
		}

		$result = implode(', ', array_unique($keyWords));

		return self::truncate($result, $maxLen, '');
	}

	/** @return string[] */
	public static function findEmails(string $text): array
	{
		$result = [];
		preg_match_all('/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i', $text, $result);
		return $result[0] ?? [];
	}

	/**
	 * @param string|string[] $needle
	 */
	public static function contains(string $haystack, $needle): bool
	{
		if (!is_array($needle)) {
			$needle = [$needle];
		}
		return self::containsArray($haystack, $needle) !== null;
	}

	public static function containsArray(string $haystack, array $needle): ?string
	{
		foreach ($needle as $query) {
			if (parent::contains($haystack, $query)) {
				return $query;
			}
		}
		return null;
	}

	public static function ipInRange(string $ip, string $range): bool
	{
		if (strpos($range, '/') == false) {
			$range .= '/32';
		}
		// $range je v IP/CIDR formatu (127.0.0.1/24)
		list($range, $netmask) = explode('/', $range, 2);
		$range_decimal = ip2long($range);
		$ip_decimal = ip2long($ip);
		$wildcard_decimal = pow(2, (32 - $netmask)) - 1;
		$netmask_decimal = ~$wildcard_decimal;
		return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
	}

	public static function validateEmail(string $email): bool
	{
		$correctDomains = [
			'gmail.com',
			'yahoo.com',
			'yahoo.ru',
			'yahoo.it',
			'icloud.com',
			'hotmail.com',
			'hotmail.es',
			'citromail.hu',
			'freemail.hu',
			'onet.pl',
			'wp.pl',
		];

		$invalidDomains = [
			'acloud.com',
			'ahoo.com',
			'cirtomail.hu',
			'citromil.hu',
			'e-mail.com',
			'email.com',
			'facebook.com',
			'fmail.com',
			'freemai.hu',
			'freemali.hu',
			'freenail.hu',
			'gail.com',
			'gamai.com',
			'gamail.com',
			'gamal.com',
			'gameil.com',
			'gamel.com',
			'gamil.com',
			'ganil.com',
			'gemai.com',
			'gimail.com',
			'gimail.ro',
			'gimeil.com',
			'gimel.com',
			'ginel.com',
			'gma.com',
			'gmai.com',
			'gmail.co.com',
			'gmail.com.com',
			'gmailyahoo.com',
			'gmaio.com',
			'gmajl.com',
			'gmalil.com',
			'gmaul.com',
			'gmeil.com',
			'gmeil.pl',
			'gmil.com',
			'gml.com',
			'gmsil.com',
			'gnail.com',
			'golmail.com',
			'hahoo.com',
			'hitmail.com',
			'hom.com.com',
			'hormail.es',
			'hotmi.com',
			'hotmsil.de',
			'hotnail.hu',
			'iahoo.com',
			'iahoo.it',
			'iclaud.com',
			'iclod.com',
			'iclud.com',
			'incloud.com',
			'jmail.com',
			'mil.com',
			'onet.com.pl',
			'stonline.sk',
			'uahoo.com',
			'vp.pl',
			'yahho.com',
			'yaho.com',
			'yaho.ro',
			'yahoi.com',
			'yahoo.co.com',
			'yahoo.com.com',
			'yahoomayl.com',
			'yahooo.com',
			'yahuu.com',
			'yaoo.com',
			'yhau.com',
			'yhoo.com',
			'yhoo.com.com',
			'yohoo.com',
			'yoo.com'
		];

		$correctDomains = str_replace('.', '\.', implode('|', $correctDomains));

		list(, $domain) = explode('@', $email);

		if (!checkdnsrr($domain)) {
			return false;
		}
		if (preg_match('/[0-9]+(' . $correctDomains . ')/i', $domain)) {
			return false;
		}
		if (preg_match('/(' . $correctDomains . ')\..*/i', $domain)) {
			return false;
		}
		if (in_array($domain, $invalidDomains)) {
			return false;
		}
		return true;
	}
}
