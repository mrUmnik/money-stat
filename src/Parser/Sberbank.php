<?php
/**
 * Created by mr.Umnik.
 */

namespace MoneyStat\Parser;


use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use MoneyStat\Entity\Transaction;
use Psr\Container\ContainerInterface;

class Sberbank
{
	const TABLE_DELIMITER = '--------------------+-----+-----+-------+--------------------------+---------------+--------------';

	const TOTAL_MARKER = '               *****************';

	protected $container;
	protected $cellRanges = [
		[0, 19],
		[20, 5],
		[26, 7],
		[34, 6],
		[41, 22],
		[64, 3],
		[68, 15],
		[84, 18]
	];

	public function __construct(ContainerInterface $c)
	{
		$this->container = $c;

		$columns = explode('+', self::TABLE_DELIMITER);
		$pos = 0;
		foreach ($columns as $column) {
			$cellRanges[] = [$pos, strlen($column)];
			$pos = $pos + strlen($column) + 1;
		}
		$cellRanges[1][0]--; // даты занимают нестандартное количество символов
		$cellRanges[2][0]--;
		$cellRanges[2][1] += 2;
	}

	public function parse($filename)
	{
		$fileHandler = fopen($filename, 'r');
		if (!$fileHandler) {
			$this->container->logger->error("Can not open file $filename");
			return;
		}
		$rows = array();
		$tableStarted = false;
		$tableBodyStarted = false;
		while ($row = fgets($fileHandler)) {
			$row = iconv('windows-1251', 'utf8', $row);
			if (strpos($row, self::TABLE_DELIMITER) === 0) {
				if (!$tableStarted) {
					$tableStarted = true;
					continue;
				}
				if (!$tableBodyStarted) {
					$tableBodyStarted = true;
					continue;
				}
				$this->parsePage($rows);

				$tableStarted = false;
				$tableBodyStarted = false;
				$rows = [];
				continue;
			}
			if (strpos($row, self::TOTAL_MARKER) === 0) {
				continue;
			}
			if ($tableBodyStarted) {
				$rows[] = $this->extractCells($row);
			}
		}
	}

	protected function extractCells($row)
	{
		$result = array();
		foreach ($this->cellRanges as $i => $range) {
			$cellValue = (substr($row, $range[0], $range[1]));
			$result[] = $i == 4 ? $cellValue : trim($cellValue);
		}
		return $result;
	}

	protected function parsePage(array $rows)
	{
		$cardName = $rows[1][0];
		$rowCounter = 0;
		while (isset($rows[$rowCounter])) {
			$extraCounter = $rowCounter + 1;
			while ($rows[$extraCounter][1] == '' && isset($rows[$extraCounter])) {
				$rows[$rowCounter][4] .= $rows[$extraCounter][4];
				unset($rows[$extraCounter]);
				$extraCounter++;
			}
			$country = '';
			$city = '';
			$description = $rows[$rowCounter][4];
			$description = trim($description);
			if (preg_match('/ ([A-Z]{2,3})$/', $description, $arCountryMatches)) { // в описании есть код страны
				$country = $arCountryMatches[1];
				$description = substr($description, 0, -strlen($country));
				if (strlen($description) == 38 || strlen($description) == 39) { // нормальная транакция вида "Покупка картой"
					$city = trim(substr($description, 25));
					$description = substr($description, 0, 25);
				}
			}
			$description = trim($description);

			$sum = $rows[$rowCounter][7];
			if (substr($sum, -2) == 'CR') {
				$income = 1;
				$sum = substr($sum, 0, -2);
			} else {
				$income = 0;
			}
			$transactionParams = [
				'card' => $cardName,
				'transaction_date' => $this->getDate($rows[$rowCounter][1] . substr($rows[$rowCounter][2], -2)),
				'processed_date' => $this->getDate($rows[$rowCounter][2]),
				'operation_id' => $rows[$rowCounter][3],
				'description' => $description,
				'currency' => $rows[$rowCounter][5],
				'sum_in_currency' => floatval($rows[$rowCounter][6]),
				'sum' => floatval($sum),
				'country' => $country,
				'city' => $city,
				'income' => $income
			];
			$transaction = Transaction::firstOrCreate($transactionParams);

			$rowCounter = $extraCounter;
		}
	}

	protected function getDate($dateStr)
	{
		$months = array(
			'ЯНВ' => '01',
			'ФЕВ' => '02',
			'МАР' => '03',
			'АПР' => '04',
			'МАЙ' => '05',
			'ИЮН' => '06',
			'ИЮЛ' => '07',
			'АВГ' => '08',
			'СЕН' => '09',
			'ОКТ' => '10',
			'НОЯ' => '11',
			'ДЕК' => '12',
		);
		$day = substr($dateStr, 0, 2);
		$month = $months[substr($dateStr, 2, 3)];
		if (!$month) {
			throw new InvalidDateException('MONTH', $dateStr);
		}
		$year = '20' . substr($dateStr, 5);

		return Carbon::createFromDate($year, $month, $day, 'GMT')->setTime(0, 0, 0);
	}
}