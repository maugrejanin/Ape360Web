<?php

class Sheet{

	static private $obj_php_excel, $config;

	static public function run($file_name, array $data, array $label_columns = [], array $config = []){
		Autoload::clear();
		include_once(dirname(__FILE__) . '/../lib/PHPExcel.php');//O novo autoload é definido aqui

		$data = self::treatData($data, $file_name, $label_columns);

		self::setConfig($config);
		self::$obj_php_excel = new PHPExcel();

		self::printout($file_name, $data);

		Autoload::clear();
		Autoload::register();
	}

	static public function treatData(array $data, $file_name, $label_columns){
		$dim = array_count_dim($data);

		if($label_columns){
			$real_columns = array_keys(current($data));
			$labels = array_sub_order($label_columns, $real_columns);

			if(sizeof($labels) != sizeof($real_columns)){
				$columns = array_keys($labels);
				$data = array_map(function($record) use($columns){
					return array_sub($record, $columns);
				}, $data);
			}

			array_unshift($data, array_values($labels));
		}

		if($dim == 3)
			return $data;
		elseif($dim == 2){
			if(strstr($file_name, '.') === false)
				$aba_name = $file_name;
			else{
				$file_name_parts = explode('.', $file_name);
				array_pop($file_name_parts);//remove a extensão
				$aba_name = implode('.', $file_name_parts);
			}

			return [
				$aba_name => $data
			];
		}else
			throw new Exception("A class Sheet não suporta mais que 3 ou menos que 2 dimensões.");
	}

	static private function setConfig(array $config){
		self::$config = array_replace_recursive([
			'body_style' => [
				'borders' => [
					'outline' => [
						'style' => PHPExcel_Style_Border::BORDER_NONE,
						'color' => 	['argb' => 'FFFFFFFF'],
					],
				],
				'alignment' => [
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				],
				'font' => [
					'bold'  => false,
					'color' => 	['rgb' => '012438'],
					'size'  => 8,
					'name'  => 'Arial'
				]
			],
			//---------------------------------------------------------------------
			'header_style' => [
				'font' => [
					'bold' => true
				],
				'alignment' => [
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				],
				// 'borders' => [
				// 	'outline' => [
				// 		'style' => PHPExcel_Style_Border::BORDER_THIN,
				// 		'color' => ['argb' => 'FFFFFFFF'],
				// 	],
				// ],
				// 'fill' => [
				// 	'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
				// 	'rotation' => 90,
				// 	'startcolor' => [
				// 		'argb' => 'FF012438'
				// 	],
				// 	'endcolor' => [
				// 		'argb' => 'FF012438'
				// 	]
				// ]
			]
		], $config);
	}

	static private function setHeaderStyle(){
		self::$obj_php_excel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray(
			self::$config['header_style']
		);
	}

	static private function setBodyStyle(){
		self::$obj_php_excel->getDefaultStyle()->applyFromArray(
			self::$config['body_style']
		);
	}

	static private function printout($file_name, array $data){
		$c = 0;
		foreach ($data as $aba_title => $aba_data) {
			if($c > 0)
				self::$obj_php_excel->createSheet($c);

			self::$obj_php_excel->setActiveSheetIndex($c);

			self::setBodyStyle();
			self::setHeaderStyle();

			self::$obj_php_excel->getActiveSheet()->fromArray($aba_data, null, 'A1');
			self::$obj_php_excel->getActiveSheet()->setTitle($aba_title);
			$c++;
		}

		ob_end_clean();
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_name );
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$writer = PHPExcel_IOFactory::createWriter(self::$obj_php_excel, 'Excel5');
		$writer->save('php://output');
		exit;
	}

}