<?php
	
	require_once('PHPExcel.php');
	require_once('PHPExcel/Writer/Excel2007.php');

	Class out_excel{

		public $language;
		public $field_language;

		function __construct(){
	       	$this->excel = new PHPExcel;
	        $this->language = array();
	        $this->excel->setActiveSheetIndex(0);
	        $style_array = array(
	            'alignment' => array(
	                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	            ),
	            'font' => array(
	                'bold' => true,
	            )
	        );
	        $style = array(
	            'columndimension' => array(
	                'width' => 'auto',
	            )
	        );
	        $this->excel->getActiveSheet()->getStyle('A:Z')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	        $this->excel->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($style_array);
	        $ord = 65;
	        for($i=$ord;$i<91;$i++){
	            $this->excel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(20);
	        }
	    }

	    public function output($first_filed,$output_data,$filename){
            $this->first_filed($first_filed);//設置第一欄位
            $this->set_data($output_data);
	        $this->outputexcel($filename);
	    }

	    public function first_filed($first_filed){
	    	foreach ($first_filed as $k => $v) {
	    		if(isset($this->language[$v])){
	    			$first_filed[$k] = $this->language[$v];
	    		}
	    	}
	        $this->excel->getActiveSheet()->fromArray($first_filed, NULL, 'A1');
	    }

	    public function set_data($data){
	        $i=1;
	        foreach ($data as $k => $v) {
	            $i++;
	            $j=65;
	            foreach ($v as $k2 => $v2) {
	      
	            	
	            	if(isset($this->field_language[$k2])){
	            		if(isset($this->field_language[$k2][$v2])){
	            			$v2 = $this->field_language[$k2][$v2];	
	            		}
	            	}
	                if(strlen($v2)>10){
	                    $this->excel->getActiveSheet()->setCellValueExplicit(chr($j).$i,$v2,PHPExcel_Cell_DataType::TYPE_STRING);    
	                }else{
	                    $this->excel->getActiveSheet()->setCellValue(chr($j).$i,$v2);
	                }
	                $j++;
	            }            
	        }
	    }

	    public function outputexcel($filename=1) {
	        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	        header('Content-Disposition: attachment;filename="'.$filename.date('YmdHi').'.xls"');
	        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
	        $objWriter->save("php://output");
	    }


	}

?>