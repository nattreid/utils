<?php

declare(strict_types=1);

namespace NAttreid\Utils;

use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Mapper\Dbal\DbalCollection;

/**
 * Pomocna trida pro hashovani
 *
 * @author Attreid <attreid@gmail.com>
 */
class Hasher
{

	private $salt;

	/**
	 * Konstruktor tridy
	 * @param string $salt
	 */
	public function __construct(string $salt)
	{
		$this->salt = $salt;
	}

	/**
	 * Zahashuje retezec
	 * @param mixed $string
	 * @return string
	 */
	public function hash($string): string
	{
		return hash('sha256', $string . $this->salt);
	}

	/**
	 * Vyhleda podle hashe
	 * @param Selection|QueryBuilder|DbalCollection $data
	 * @param string|array $columns
	 * @param string $hash
	 * @return Selection|QueryBuilder
	 * @throws InvalidArgumentException
	 */
	public function hashSQL($data, $columns, string $hash)
	{
		if (is_string($columns)) {
			$columns = [$columns];
		}

		if (is_array($columns)) {
			if ($data instanceof DbalCollection) {
				$data = $data->getQueryBuilder();
			}

			if ($data instanceof Selection) {
				$col = '';
				foreach ($columns as $column) {
					if ($col !== '') {
						$col .= ',';
					}
					$col .= "`$column`";
				}
				return $data->where("SHA2(CONCAT($col,  '{$this->salt}'), 256)", $hash);
			} elseif ($data instanceof QueryBuilder) {
				return $data->andWhere('SHA2(CONCAT(%column[], %s), 256) = %s', $columns, $this->salt, $hash);
			}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * Zkontroluje zda je hash vytvoren z daneho retezce
	 * @param mixed $string
	 * @param string $hash
	 * @return bool
	 */
	public function check($string, string $hash): bool
	{
		return hash_equals($this->hash($string), $hash);
	}

}
