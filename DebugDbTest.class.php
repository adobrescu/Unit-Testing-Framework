<?php

class DebugDbTest extends DebugTest
{
	public function generateRandomString($length)
	{
		return 'axxa'.chr(rand(60,90));
		$str='';
		for($i=0; $i<$length; $i++)
		{
			/*
			$ord=rand(0, 52)*100+rand(1,100);
			
			$chr=mb_convert_encoding('&#'.$ord, 'UTF-8', 'HTML-ENTITIES');
			
			
			$str.=$chr;
			*/
			$str.=chr(rand(60, 90));
		}
		return $str;
	}
	
	public function generateRandomValidColumnValue($db, $tableName, $columnName)
	{
		$recordSchema=$db->getRecordSchema($tableName);
		$columnDef=$recordSchema[IDX_COLUMNS][$columnName];
		$length=isset($columnDef[FLD_IDX_LENGTH])?$columnDef[FLD_IDX_LENGTH]:null;
		$value=null;
		switch($columnDef[FLD_IDX_TYPE])
		{
			case FLD_TYPE_SET:
				$iMax=count($columnDef[FLD_IDX_SET])>2?2:1;
							
				$value=$columnDef[FLD_IDX_SET][rand(0, $iMax/2-1)];
				$value.=','.$columnDef[FLD_IDX_SET][rand($iMax/2, count($columnDef[FLD_IDX_SET])-1)];
								
				break;
			case FLD_TYPE_ENUM:
				$value=$columnDef[FLD_IDX_SET][rand(0, count($columnDef[FLD_IDX_SET])-1)];
				
				break;
			case FLD_TYPE_BINARY:
				$value=str_repeat(chr(rand(60,100)), $length);
				$value=str_repeat('\\', $length);
				break;
			case FLD_TYPE_BIT:
				$value='1';
				for($i=1; $i<$length; $i++)
				{
					$value.=rand(0,1);
				}
				break;
			case FLD_TYPE_BLOB:
				$value='Lorem';
				break;
			case FLD_TYPE_STRING:
				
				if(isset($recordSchema[UNIQUE_FIELDS]))
				{
					
					foreach($recordSchema[UNIQUE_FIELDS] as $indexName=>$columns)
					{
						
						if(isset($columns[$columnName]))
						{
							//echo $uqColumnName.'<br>';
							$value=uniqid();
							break;
						}
					}
				}
				if(is_null($value))
				{
					$value=$this->generateRandomString($length);
				}
				break;
			case FLD_TYPE_DATE:
					//1000-01-01 00:00:00', FLD_IDX_MAX_VALUE => '9999-12-31 23:59:59'
				$year=rand(1,12);
				$day=rand(1,28);
				$value=rand(1000,9999).'-'.($year<10?'0':'').$year.'-'.($day<10?'0':'').$day;
				break;
			case FLD_TYPE_DATETIME:
				
				$year=rand(1,12);
				$day=rand(1,28);
				$value=rand(1000,9999).'-'.($year<10?'0':'').$year.'-'.($day<10?'0':'').$day;
				
				$hour=rand(0,23);
				$min=rand(0,59);
				$sec=rand(0,59);
				
				$value.=' '.($hour<10?'0':'').$hour.':'.($min<10?'0':'').$min.':'.($sec<10?'0':'').$sec;
				break;
			case FLD_TYPE_TIMESTAMP:
				$month=rand(1,12);
				$day=rand(1,28);
				$year=rand(1970,2038);
				if($year==2038)
				{
					$value='2038-01-19 03:14:07';
					if($length)
					{
						$decimalPart=sprintf('%01.6f', rand(0, pow(10, $length-1))/pow(10, $length-1));
						$value.=substr($decimalPart, 1);
						//$value.='.'.rand(0, 
	//					/die(''.$length);
					}
					return $value;
				}
				else
				{
					$value=$year.'-'.($month<10?'0':'').$month.'-'.($day<10?'0':'').$day;
				}
				$hour=rand(0,23);
				$min=rand(0,59);
				$sec=rand(0,59);
				
				$value.=' '.($hour<10?'0':'').$hour.':'.($min<10?'0':'').$min.':'.($sec<10?'0':'').$sec;
				if($length)
				{
					$decimalPart=sprintf('%01.6f', rand(0, pow(10, $length-1))/pow(10, $length-1));
					$value.=substr($decimalPart, 1);
					//$value.='.'.rand(0, 
//					/die(''.$length);
				}
				break;
			case FLD_TYPE_DECIMAL:
				$value=rand(1,9);
				for($i=1; $i<$length-$columnDef[FLD_IDX_DIGITS]; $i++)
				{
					$value.=rand(0,9);
				}
				if($columnDef[FLD_IDX_DIGITS])
				{
					$value.='.';
					for($i=0; $i<$columnDef[FLD_IDX_DIGITS]; $i++)
					{
						$value.=rand(0,9);
					}
				}
				break;
			case FLD_TYPE_FLOAT:
				$value=rand(-100000,100000)/1000;
				break;
			case FLD_TYPE_INTEGER:
				$value=rand($columnDef[FLD_IDX_MIN_VALUE],$columnDef[FLD_IDX_MAX_VALUE]-1);
				break;
			case FLD_TYPE_TIME:
				$value='08:05:00';
				break;
			
							
		}
		return $value;
	}
	public function generateRandomValidRecordData($db, $tableName, $requiredColumnsOnly=false, &$skipMutualFKs=array())
	{
		$recordSchema=$db->getRecordSchema($tableName);
		
		foreach($recordSchema[IDX_COLUMNS] as $columnName=>$columnDef)
		{
			if(isset($recordSchema[TBL_IDX_DEFS_AUTO_INCREMENT]) && $recordSchema[TBL_IDX_DEFS_AUTO_INCREMENT]==$columnName)
			{
				continue;
			}
			$arrRecord[$columnName]=$this->generateRandomValidColumnValue($db, $tableName, $columnName);
		}
		return $arrRecord;
		
		
		
	}
	public function generateRandomValidMutualRecordData($db, $tableName, $requiredColumnsOnly=false, &$mutualRecord=array(), $fkName='', &$skipMutualFKs=array())
	{
		$recordSchema=$db->getRecordSchema($tableName);
		//print_r($recordSchema); exit();
		foreach($recordSchema[IDX_COLUMNS] as $columnName=>$columnDef)
		{
			$isMutual=false;
			if(isset($recordSchema[FK_1_1_PARENT]))
			{
				foreach($recordSchema[FK_1_1_PARENT] as $fkName=>$fk)
				{
					if(isset($fk['columns'][$columnName]) && isset($skipMutualFKs[$fkName]))
					{
						$isMutual=true;
						break;
					}
				}
			}
			if($isMutual || (isset($recordSchema[TBL_IDX_DEFS_AUTO_INCREMENT]) && $recordSchema[TBL_IDX_DEFS_AUTO_INCREMENT]==$columnName))
			{
				continue;
			}
			$arrRecord[$columnName]=$this->generateRandomValidColumnValue($db, $tableName, $columnName);
		}
		
		$mutualRecord[$fkName]['host']=$db->getHostName();
		$mutualRecord[$fkName]['database']=$db->getName();
		$mutualRecord[$fkName]['table']=$tableName;
		$mutualRecord[$fkName]['record']=$arrRecord;
		
		if(isset($recordSchema[FK_1_1_CHILDREN]))
		{
			foreach($recordSchema[FK_1_1_CHILDREN] as $fkName=>$fk)
			{
				if(isset($skipMutualFKs[$fkName]))
				{
					continue;
				}
				$skipMutualFKs[$fk['name']]=1;
				$this->generateRandomValidMutualRecordData($db, $fk['table'], $requiredColumnsOnly, 
						$mutualRecord, $fk['name'], $skipMutualFKs);
			}
		}
		return $mutualRecord;
	}
	public function dummyInsert($db, $tableName, $arrRecord)
	{
		$queryColumnNames=$queryColumnValues='';
		foreach($arrRecord as $columnName=>$columnValue)
		{
			
			$queryColumnNames.=($queryColumnNames?',':'').$columnName;
			if(substr($columnValue, -1)=='\'')
			{
				$queryColumnValues.=($queryColumnValues?',':'').$columnValue;
			}
			else
			{
				$queryColumnValues.=($queryColumnValues?',':'').'\''.addslashes($columnValue).'\'';
			}
		}
		
		$query='INSERT INTO '.$tableName.'
			('.$queryColumnNames.')
			VALUES
			('.$queryColumnValues.')';
		$db->query($query);
	}
	public function printRecordset($arrRecordset)
	{
		if(!is_array($arrRecordset))
		{
			return;
		}
		$html='<table cellspacing="0" cellpadding="8" border="1">
			<caption style="text-align: left; padding: 10px;">#records: '.count($arrRecordset).'</caption>';
		$outputHeader=true;
		foreach($arrRecordset as $arrRecord)
		{
			if($outputHeader)
			{
				$html .='<thead>
					<tr>';
				
				foreach($arrRecord as $columnName=>$columnValue)
				{
					$html.='<th>'.$columnName.'</th>';
				}
				$html.='
						</tr>
					</thead>';
				
				$outputHeader=false;
			}
			$html .='<tr>';
			foreach($arrRecord as $columnName=>$columnValue)
			{
				$html.='<td>'.$columnValue.'</th>';
			}
			$html .='</tr>';
		}
		$html.='</table>';
		echo $html;
	}
	public function ASSERT_RECORD_EXISTS($database, $tableName, $record, $debug=false)
	{
		
		$where='';
		$schema=$database->getRecordSchema($tableName);
		$columnNames='';
		foreach($record as $columnName=>$columnValue)
		{
			if(!isset($schema[alib\model\Database::IDX_COLUMNS][$columnName]))
			{
				continue;
			}
			switch($schema[alib\model\Database::IDX_COLUMNS][$columnName][alib\model\Database::IDX_TYPE])
			{
				case alib\model\Database::TYPE_BIT:
					$columnNames.=($columnNames?',':'').'BIN('.alib\model\Database::SQL_ID_QUOTE.$columnName.alib\model\Database::SQL_ID_QUOTE.') AS '.alib\model\Database::SQL_ID_QUOTE.$columnName.alib\model\Database::SQL_ID_QUOTE;
									
					//$where.=($where?' AND ':'').'BIN('.$tableName.'.'.$columnName.')=\''.addslashes($columnValue).'\''."\n";
					break;
				
					
				default:
					$columnNames.=($columnNames?',':'').alib\model\Database::SQL_ID_QUOTE.$columnName.alib\model\Database::SQL_ID_QUOTE;
					
					
					//$where.=($where?' AND ':'').$tableName.'.'.$columnName.'=\''.addslashes($columnValue).'\''."\n";
			}
			$columnNames.="\n";
			
			
		}
		foreach($schema[alib\model\Database::IDX_ID_COLUMNS] as $indexName=>$columns)
		{
			$idFound=true;
			foreach($columns as $columnName=>$null)
			{
				if(!array_key_exists($columnName, $record))
				{
					$idFound=false;
					break;
				}
			}
			if($idFound)
			{
				$idIndexName=$indexName;
				break;
			}
		}
		
		
		foreach($schema[alib\model\Database::IDX_ID_COLUMNS][$idIndexName] as $columnName=>$null)
		{
			$where .= ($where?' AND ':'').alib\model\Database::SQL_ID_QUOTE.$columnName.alib\model\Database::SQL_ID_QUOTE.'=\''.addslashes($record[$columnName]).'\'';
		}
		
		
		$query='SELECT '.$columnNames.'
			FROM '.$tableName.'
			WHERE '.$where;
		
		//echo htmlentities($query).'<hr>';
		$dbRecord=$database->loadArrayRecord($query);
		
		$this->ASSERT_EQUALS($record, $dbRecord, $strict=false);
		
	}
	public function ASSERT_RECORD_DOESNT_EXIST($database, $tableName, $record)
	{
		$where='';
		foreach($record as $columnName=>$columnValue)
		{
			$where.=($where?' AND ':'').$tableName.'.'.$columnName.'=\''.addslashes($columnValue).'\'';
		}
		
		$query='SELECT *
			FROM '.$tableName.'
			WHERE '.$where;
		
		
		$dbRecord=$database->loadArrayRecord($query);
		
		$this->ASSERT_FALSE($dbRecord?true:false, $dbRecord, $strict=false);
		
	}
	public function ASSERT_MUTUAL_RECORD_EXISTS($database, $tableName, $arrMutualRecord, &$skipMutualFKs=array())
	{
		//echo $tableName.'<br>';
		$recordSchema=$database->getRecordSchema($tableName);
		
		foreach($recordSchema[IDX_COLUMNS] as $columnName=>$columnDef)
		{
			$arrRecord[$columnName]=$arrMutualRecord[$columnName];
		}
		//print_r($arrRecord);
		$this->ASSERT_RECORD_EXISTS($database, $tableName, $arrRecord, true);
		
		if(!isset($recordSchema[FK_1_1_CHILDREN]))
		{
			return;
		}
		foreach($recordSchema[FK_1_1_CHILDREN] as $fkName=>$fk)
		{
			if(isset($skipMutualFKs[$fkName]))
			{
				continue;
			}
			$skipMutualFKs[$fkName]=1;
			
			foreach($fk['columns'] as $columnName=>$mutualColumnName)
			{
				$arrMutualRecord[$mutualColumnName]=$arrMutualRecord[$columnName];
			}
			
			$this->ASSERT_MUTUAL_RECORD_EXISTS($database, $fk['table'], $arrMutualRecord, $skipMutualFKs);
		}
	}
}