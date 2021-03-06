<?php


/**
 * Базов драйвер за вид партида 'хендлър на документ с дата'
 *
 *
 * @category  bgerp
 * @package   batch
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @title Хендлър на документ с дата
 */
class batch_definitions_Document extends batch_definitions_Varchar
{
	
	
	/**
	 * Връща автоматичния партиден номер според класа
	 *
	 * @param mixed $documentClass - класа за който ще връщаме партидата
	 * @param int $id - ид на документа за който ще връщаме партидата
	 * @return mixed $value - автоматичния партиден номер, ако може да се генерира
	 */
	public function getAutoValue($documentClass, $id)
	{
		$Class = cls::get($documentClass);
		expect($dRec = $Class->fetchRec($id));
		
		$handle = mb_strtoupper($Class->getHandle($dRec->id));
		$date = $dRec->{$Class->valiorFld};
		$date = str_replace('-', '', $date);
		
		$res = "{$date}-{$handle}";
		
		return $res;
	}
	
	
	/**
	 * Проверява дали стойността е невалидна
	 *
	 * @param string $value - стойноста, която ще проверяваме
	 * @param quantity $quantity - количеството
	 * @param string &$msg - текста на грешката ако има
	 * @return boolean - валиден ли е кода на партидата според дефиницията или не
	 */
	public function isValid($value, $quantity, &$msg)
	{
		if(!preg_match("/^[0-9]{8}[\-]{1}[A-Z]{3}[0-9]+/", $value, $matches)){
			$date = str_replace('-', '', dt::today());
			
			$msg = "Формата трябва да е във вида на|* {$date}-SAL1";
			return FALSE;
		}
		
		return parent::isValid($value, $quantity, $msg);
	}
}