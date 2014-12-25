<?php



/**
 * Клас 'plg_GroupByDate' - Плъгин за групиране на записите на модел по общо поле
 * 
 * Трябва да е зададено ''. В таблицата на модела се добавя по един ред със встойността на това поле,
 * а всички записи които я имат са под нея, така имаме групирани записи.
 *
 *
 * @category  ef
 * @package   plg
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @link
 */
class plg_GroupByField extends core_Plugin
{
	
	
	/**
	 *  Преди рендиране на лист таблицата
	 */
	public static function on_BeforeRenderListTable($mvc, &$res, $data)
	{
		// Ако няма записи, не правим нищо
		if(!count($data->recs)) return;
		 
		$recs = &$data->recs;
		
		// Ако не е зададено поле за групиране, не правим нищо
		if(!($field = $mvc->groupByField)) return;
		 
		// Премахваме като колона полето, което ще групираме
		unset($data->listFields[$field]);
		 
		// Колко е броя на колоните
		$columns = count($listFields);
		 
		$groups = array();
		 
		// Изчличаме в масив всички уникални стойностти на полето
		foreach ($recs as $index => $rec1){
			$groups[$rec1->$field] = $data->rows[$index]->$field;
		}
		 
		$rows = array();
		 
		// За всяко поле за групиране
		foreach ($groups as $groupId => $groupVerbal){
			$groupVerbal = isset($groupVerbal) ? "<b>{$groupVerbal}</b>" : '<div style = "height:12px"></div>';
	
			// Създаваме по един ред с името му, разпънат в цялата таблица
			$rowAttr['class'] .= ' group-by-field-row';
			$rows['|' . $groupId] = ht::createElement('tr',
					$rowAttr,
					new ET("<td style='padding-top:9px;padding-left:5px;' colspan='{$columns}'>" . $groupVerbal . "</td>"));
	
			// За всички записи
			foreach ($recs as $id => $rec){
				 
				// Ако стойността на полето им за групиране е същата като текущото
				if($rec->$field == $groupId){
	
					// Скриваме това поле от записа, и поставяме реда под групиращото поле
					unset($data->rows[$id]->$field);
					$rows[$id] = clone $data->rows[$id];
	
					// Веднъж групирано, премахваме записа от старите записи
					unset($data->rows[$id]);
				}
			}
		}
		
		$data->rows = $rows;
	}
}