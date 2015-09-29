<?phpnamespace Home\Event;use Think\Controller;class ExcelEvent extends Controller {		// $type: issue_default, issue_with_img, login_record	public function export($cols, $rows, $fname, $type='issue_default') {		error_reporting(E_ALL);		import('Vendor.PHPExcel.Classes.PHPExcel', '', '.php');		$objPHPExcel = new \PHPExcel();		$objPHPExcel->getProperties()->setCreator("sys")									 ->setLastModifiedBy("sys")									 ->setTitle("Excel Document")									 ->setSubject("Excel Document")									 ->setDescription("Excel Document")									 ->setKeywords("openxml php")									 ->setCategory("excel file");									 		$objPHPExcel->setActiveSheetIndex(0);		if($type == 'issue_with_img') {			$Col_C_Max_Width = 80;// 80/40 * 6 = 12		}				$col_cfg = array();				foreach($cols as $k=>$col) {			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $col);		}				foreach($rows as $k=>$row) {			foreach($row as $j=>$val) {				if ($type == 'issue_default' && is_array($val)) {					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $k+2, $val['text']);					$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($j, $k+2)->getHyperlink()->setUrl($val['url']);				}else if ($type == 'issue_default' && $j == 10) { // target code					$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($j, $k+2)->setValueExplicit($val, \PHPExcel_Cell_DataType::TYPE_STRING);				}else if($type == 'issue_with_img' && $j == 3) { // image					if (!empty($val) && file_exists($val)) {						// Calculate coordinate						if ($j >= 26) {							$quotient = floor($j / 26);							$mod = ($j % 26);														$char = chr($quotient-1 + 65) . chr($mod + 65);						}else {							$char = chr($j + 65);						}												$img = $val;						$img_info = getimagesize($img);						$height = $img_info[1] > 200 ? 200 : $img_info[1];						$width = $img_info[0] / $img_info[1] * $height;						$width > $Col_C_Max_Width && $Col_C_Max_Width = $width;						$objDrawing = new \PHPExcel_Worksheet_Drawing();						$objDrawing->setName('ͼƬ');						$objDrawing->setDescription('ͼƬ');						$objDrawing->setPath($img);						$objDrawing->setHeight($height);						$objDrawing->setCoordinates($char . ($k+2));						$objDrawing->setOffsetX(10);						$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());						$objPHPExcel->getActiveSheet()->getRowDimension($k+2)->setRowHeight($height);					}else {						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $k+2, '');					}				}else{					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j, $k+2, $val);				}			}		}				if($type == 'issue_with_img') {			$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setWidth($Col_C_Max_Width / 40 * 6);//setAutoSize(true);		}		// Set Sheet Title		$objPHPExcel->getActiveSheet()->setTitle('Sheet1');		// Set 		$styleArray = array( 			//'font' => array( 'bold' => true, ), 			'alignment' => array( 				'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, 				'vertical' => \PHPExcel_Style_Alignment::VERTICAL_TOP			), 			/*'borders' => array( 				'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, ), 			), 			'fill' => array( 				'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 				'rotation' => 90, 				'startcolor' => array( 'argb' => 'FFA0A0A0', ), 				'endcolor' => array( 'argb' => 'FFFFFFFF', ), 			), */		);		//$objPHPExcel->getActiveSheet()->getStyle('A2:B3')->applyFromArray($styleArray);		//$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth($Col_C_Max_Width / 40 * 6);//setAutoSize(true);				/*		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 		header('Content-Disposition: attachment;filename="' . $fname . '.xlsx"'); 		header('Cache-Control: max-age=0');		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');		//$objWriter->save($fpath . '.xlsx');		$objWriter->save('php://output');*/				header('Content-Type: application/vnd.ms-excel'); 		header('Content-Disposition: attachment;filename="' . $fname . '.xls"'); 		header('Cache-Control: max-age=0'); 		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 		$objWriter->save('php://output');	}		public function importIssue($fpath, $user, $city) {		import('Vendor.PHPExcel.Classes.PHPExcel.IOFactory', '', '.php');		//require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';		$inputFileName = $fpath;		// Identify the type of $inputFileName 		$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);//exit($inputFileType);		//echo 'Loading ' , $inputFileName , ' using ' , $inputFileType , " Reader" , PHP_EOL;		// Create a new Reader of the type that has been identified 		$objReader = \PHPExcel_IOFactory::createReader($inputFileType);		// Load $inputFileName to a PHPExcel Object  		$objReader->setReadDataOnly(true);		$objPHPExcel = $objReader->load($inputFileName);		$workSheet = $objPHPExcel->getActiveSheet();		$file_cols_to_db_cols = array(			3	=> 'area1',			4	=> 'area2',			5	=> 'area3',			6	=> 'area4',			7 	=> 'come_from',			8	=> 'checker',			10	=> 'target1',			11	=> 'target2',			12	=> 'target3',			9	=> 'target_code',			15	=> 'username',			14	=> 'weight',			2	=> 'img',			13	=> 'des'		);		$ret = array(			'total' 		=> 0,			'success_num' 	=> 0,			'fail_num' 		=> 0,			'fails' 		=> array()		);		$fails = array();				$IssueEvent = A('Issue', 'Event');		foreach($workSheet->getRowIterator() as $key=>$row) {			//echo $key,'<br />';			if($key < 2) continue;			$cellIterator = $row->getCellIterator();			$cellIterator->setIterateOnlyExistingCells(false);// This loops all cells, even if it is not set. By default, only cells that are set will be iterated.			$data = array(				//'user_id'	=> $user['id'],				'city_id'	=> $city['city_id']			);			foreach($cellIterator as $k=>$cell) {				if ($k == 0) {					$data['examine_time'] = str_replace(' ', '', $cell->getValue());				}else if ($k == 1) {					$data['examine_time'] .= ' ' . str_replace(' ', '', $cell->getValue());				}else if ( isset($file_cols_to_db_cols[$k])) {					$data[$file_cols_to_db_cols[$k]] = str_replace(' ', '', $cell->getValue());				}			}			$ret['total'] += 1;			//print_r($data);			$rs = $IssueEvent->importOneRow($data);			if ($rs['code'] == 0) {				$ret['success_num'] += 1;			}else {				$ret['fail_num'] += 1;				$ret['fails'][] = array(					'seq'	=> $key,					'error'	=> $rs['error']				);			}		}		return $ret;	}		public function exportSummary($left_cols, $top_cols, $left_cols_width, $top_cols_height, $data, $fname) {		error_reporting(E_ALL);		import('Vendor.PHPExcel.Classes.PHPExcel', '', '.php');		$objPHPExcel = new \PHPExcel();		$objPHPExcel->getProperties()->setCreator("sys")									 ->setLastModifiedBy("sys")									 ->setTitle("Excel Document")									 ->setSubject("Excel Document")									 ->setDescription("Excel Document")									 ->setKeywords("openxml php")									 ->setCategory("excel file");									 		$objPHPExcel->setActiveSheetIndex(0);		//echo $left_cols_width - 1, '<br />', $top_cols_height;exit;		$left_keys = $top_keys = array();		$smry_array = $total_array = array();		// left		$n = $this->_writeLeftCols($objPHPExcel, $left_cols, $top_cols_height+1, 0, $left_cols_width - 1, $left_keys, $smry_array, $total_array);		$max_line = $n - 1;		$max_left_col_char = $this->_calColTitle($left_cols_width - 1);		$left_start_row = $top_cols_height+1;				// top		$n = $this->_writeTopCols($objPHPExcel, $top_cols, $left_cols_width, 1, $top_cols_height, $top_keys);		$max_col = $n - 1;		$max_col_char = $this->_calColTitle($max_col); //$max_col - 1		$top_start_col_char = $this->_calColTitle($left_cols_width);				// data		foreach($left_keys as $v_1) {			foreach($top_keys as $v_2) {				$value = isset($data[$v_1['id']][$v_2['id']]) ? $data[$v_1['id']][$v_2['id']] : 0;				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($v_2['col'], $v_1['row'])->setValueExplicit($value, \PHPExcel_Cell_DataType::TYPE_STRING);			}		} 				// merge left top cells		$objPHPExcel->getActiveSheet()->mergeCells('A1:' . $max_left_col_char . $top_cols_height);				// Set Sheet Title		$objPHPExcel->getActiveSheet()->setTitle('Sheet1');				$style_align = array( 			//'font' => array( 'bold' => true, ), 			'alignment' => array( 				'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 				'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER			), 			/*'borders' => array( 				'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, ), 			), 			'fill' => array( 				'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 				'rotation' => 90, 				'startcolor' => array( 'argb' => 'FFA0A0A0', ), 				'endcolor' => array( 'argb' => 'FFFFFFFF', ), 			), */		);		$style_fill = array(			'fill' => array( 				'type' => \PHPExcel_Style_Fill::FILL_SOLID, 				'color' => array( 'rgb' => 'F0F7FF', ), 			),		);		$style_fill2 = array(			'fill' => array( 				'type' => \PHPExcel_Style_Fill::FILL_SOLID, 				'color' => array( 'rgb' => 'BFE0FF', ), 			),		);		$style_border = array(			'borders' => array( 				'allborders' => array( 					'style' => \PHPExcel_Style_Border::BORDER_THIN, 					'color'	=> array( 'rgb' => '666666'),				), 			),		);				$styleM = array_merge($style_align, $style_fill, $style_border);		//print_r($styleM);exit;		$objPHPExcel->getActiveSheet()->getStyle('A' . $left_start_row . ':' . $max_left_col_char . $max_line)->applyFromArray($styleM);		$objPHPExcel->getActiveSheet()->getStyle($top_start_col_char . '1:' . $max_col_char . $top_cols_height)->applyFromArray($styleM);		$objPHPExcel->getActiveSheet()->getStyle($top_start_col_char . $left_start_row . ':' . $max_col_char . $max_line)->applyFromArray(array_merge($style_align, $style_border));		foreach($smry_array as $val) {			$char = $this->_calColTitle($val['col']);			$objPHPExcel->getActiveSheet()->getStyle($char . $val['row'] . ':' . $max_col_char . $val['row'])->applyFromArray($style_fill2);		}		foreach($total_array as $val) {			$char = $this->_calColTitle($val['col']);			$objPHPExcel->getActiveSheet()->getStyle($char . $val['row'] . ':' . $max_col_char . $val['row'])->applyFromArray($style_fill2);		}				foreach(range('A', $max_col_char) as $columnID) {			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setWidth(17); //setAutoSize(true);		}		foreach(range(1, $max_line) as $rowID) {			$objPHPExcel->getActiveSheet()->getRowDimension($rowID)->setRowHeight(30);		}				header('Content-Type: application/vnd.ms-excel'); 		header('Content-Disposition: attachment;filename="' . $fname . '.xls"'); 		header('Cache-Control: max-age=0'); 		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); 		$objWriter->save('php://output');	}		private function _writeTopCols($objPHPExcel, $top_cols, $line_to_left, $row_i, $top_max_row, &$top_keys) {		foreach($top_cols as $k=>$v) {			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($line_to_left, $row_i, $v['text']);						if (!empty($v['children'])) {				$this->_writeTopCols($objPHPExcel, $v['children'], $line_to_left, $row_i+1, $top_max_row, $top_keys);			}else {				$top_keys[] = array(					'id'	=> $v['id'],					'col'	=> $line_to_left				);				if ($row_i < $top_max_row) {					$char = $this->_calColTitle($line_to_left);					$objPHPExcel->getActiveSheet()->mergeCells($char . $row_i . ':' . $char . $top_max_row);				} 			}						if ($v['descendant_num'] > 0) {				$start_char = $this->_calColTitle($line_to_left);				$end_char = $this->_calColTitle($line_to_left + $v['descendant_num'] - 1);				$objPHPExcel->getActiveSheet()->mergeCells($start_char . $row_i . ':' . $end_char . $row_i);			}						$line_to_left += $v['descendant_num'] == 0 ? 1 : $v['descendant_num'];		}				return $line_to_left;	}		private function _writeLeftCols($objPHPExcel, $left_cols, $line_to_top, $col_i, $left_max_col, &$left_keys, &$smry_array, &$total_array) {		foreach($left_cols as $k=>$v) {			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_i, $line_to_top, $v['text']);						if (!empty($v['children'])) {				$this->_writeLeftCols($objPHPExcel, $v['children'], $line_to_top, $col_i+1, $left_max_col, $left_keys, $smry_array, $total_array);			}else {				$left_keys[] = array(					'id'	=> $v['id'],					'row'	=> $line_to_top				);								if (!empty($v['is_smry'])) {					$smry_array[] = array(						'col'	=> $col_i,						'row'	=> $line_to_top					);				}else if (!empty($v['is_total'])) {					$total_array[] = array(						'col'	=> $col_i,						'row'	=> $line_to_top					);				}								if ($col_i < $left_max_col) {					$startChar = $this->_calColTitle($col_i);					$endChar = $this->_calColTitle($left_max_col);					$objPHPExcel->getActiveSheet()->mergeCells($startChar . $line_to_top . ':' . $endChar . $line_to_top);				}			}						if ($v['descendant_num'] > 0) {				$char = $this->_calColTitle($col_i);				$objPHPExcel->getActiveSheet()->mergeCells($char . $line_to_top . ':' . $char . ($line_to_top + $v['descendant_num'] - 1));			}						$line_to_top += $v['descendant_num'] == 0 ? 1 : $v['descendant_num'];		}				return $line_to_top;	}		private function _calColTitle($j) {		if ($j >= 26) {			$quotient = floor($j / 26);			$mod = ($j % 26);										$char = chr($quotient-1 + 65) . chr($mod + 65);		}else {			$char = chr($j + 65);		}				return $char;	}}