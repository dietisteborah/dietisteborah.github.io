<?php
/**
*@author  Xu Ding
*@email   thedilab@gmail.com
*@website http://www.StarTutorial.com
**/
class Calendar {  
     
    /**
     * Constructor
     */
    public function __construct(){     
        $this->naviHref = htmlentities($_SERVER['PHP_SELF']);
    }
     
    /********************* PROPERTY ********************/  
    private $dayLabels = array("Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag","Zondag");
     
    private $currentYear=0;
     
    private $currentMonth=0;
     
    private $currentDay=0;
     
    private $currentDate=null;
     
    private $daysInMonth=0;
     
    private $naviHref= null;
	
	private $currentWeek=0;
     
    /********************* PUBLIC **********************/  
        
    /**
    * print out the calendar
    */
    public function show() {
         	
		$week = null;
        
		$day = null;
		
		$hour = null;               

        if(null==$week&&isset($_GET['week'])){
 
            $week = $_GET['week'];
         
        }else if(null==$week){
 
            $week = time();
         
        }  	
        if(null==$hour&&isset($_GET['hour'])){
			$timeArray = explode ( '-' , $_GET['hour'] );
            $hour = $timeArray[0];
			$week = $timeArray[1];
        }else if(null==$hour){
 
            $hour = date("h",time());
         
        }  			
                 
		$this->currentWeek=$week;
		/*
        $content='<div id="calendar">'.
						'<div class="box">'.
						$this->_createNaviWeek().
						'</div>'.
                        '<div class="box-content">'.
                                '<ul class="label">'.$this->_createLabels().'</ul>';   
                                $content.='<div class="clear"></div>';     
                                $content.='<ul class="dates">';    
                                 
                                $weeksInMonth = $this->_weeksInMonth($month,$year);
                                // Create weeks in a month
                                for( $i=0; $i<$weeksInMonth; $i++ ){
                                     
                                    //Create days in a week
                                    for($j=1;$j<=7;$j++){
                                        $content.=$this->_showDay($i*7+$j);
                                    }
                                }
                                 
                                $content.='</ul>';
                                 
                                $content.='<div class="clear"></div>';     
             
                        $content.='</div>';
                 
        $content.='</div>'; */
		
		        $content='<div id="calendar">'.
						'<div class="box">'.
						$this->_createNaviWeek().
						'</div>'.
                        '<div class="box-content">'.
                                '<ul class="label">'.$this->_createLabels().'</ul>';   
                                $content.='<div class="clear"></div>';     
                                $content.='<ul class="dates">';    
                                 
                                // Create hours in a day
                                for( $i=7; $i<22; $i++ ){
                                     
                                    //Create days in a week
                                    for($j=1;$j<=7;$j++){
                                        $content.=$this->_showHour($i,$j);
                                    }
                                }
                                 
                                $content.='</ul>';
                                 
                                $content.='<div class="clear"></div>';     
             
                        $content.='</div>';
                 
        $content.='</div>';
        return $content;   
    }
     
    /********************* PRIVATE **********************/ 
    /**
    * create the li element for ul
    */
	/*
    private function _showDay($cellNumber){
         
        if($this->currentDay==0){
             
            $firstDayOfTheWeek = date('N',strtotime($this->currentYear.'-'.$this->currentMonth.'-01'));
                     
            if(intval($cellNumber) == intval($firstDayOfTheWeek)){
                 
                $this->currentDay=1;
                 
            }
        }
         
        if( ($this->currentDay!=0)&&($this->currentDay<=$this->daysInMonth) ){
             
            $this->currentDate = date('Y-m-d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
             
            $cellContent = $this->currentDay;
             
            $this->currentDay++;   
             
        }else{
             
            $this->currentDate =null;
 
            $cellContent=null;
        }
             
         
        return '<li id="li-'.$this->currentDate.'" class="'.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
                ($cellContent==null?'mask':'').'">'.$cellContent.'</li>';
    }*/
	
    private function _showHour($hour,$day){    
		/*z$wday = date('w', $this->currentWeek);
		$ID = $hour."-".date('d-m-Y', $this->currentWeek - ($wday - $day)*86400);
        //$ID= $hour.$day;
        //return '<li id="li-'.$ID.'" href="'.$this->naviHref.'?hour='.sprintf('%03d',$ID).'">'.$hour.'</li>';
		return '<li> <a href="'.$this->naviHref.'?hour='.$ID.'">'.$hour.'</a></li>';
		*/
		$ID = $hour."-".$this->currentWeek;
        //$ID= $hour.$day;
        //return '<li id="li-'.$ID.'" href="'.$this->naviHref.'?hour='.sprintf('%03d',$ID).'">'.$hour.'</li>';
		return '<li> <a href="'.$this->naviHref.'?hour='.$ID.'">'.$hour.'</a></li>';
    } 
    /**
    * create navigation
    */
	/*
    private function _createNavi(){
         
        $nextMonth = $this->currentMonth==12?1:intval($this->currentMonth)+1;
         
        $nextYear = $this->currentMonth==12?intval($this->currentYear)+1:$this->currentYear;
         
        $preMonth = $this->currentMonth==1?12:intval($this->currentMonth)-1;
         
        $preYear = $this->currentMonth==1?intval($this->currentYear)-1:$this->currentYear;
         
        return
            '<div class="header">'.
                '<a class="prev" href="'.$this->naviHref.'?month='.sprintf('%02d',$preMonth).'&year='.$preYear.'">Prev</a>'.
                    '<span class="title">'.date('Y M',strtotime($this->currentYear.'-'.$this->currentMonth.'-1')).'</span>'.
                '<a class="next" href="'.$this->naviHref.'?month='.sprintf("%02d", $nextMonth).'&year='.$nextYear.'">Next</a>'.
            '</div>';
    }*/
    /**
    * create week navigation
    */
    private function _createNaviWeek(){
         
		$nextWeek = $this->currentWeek+(7*86400);
		$preWeek = $this->currentWeek-(7*86400);
        $wday = date('w', $this->currentWeek); 
        return
            '<div class="header">'.
                '<a class="prev" href="'.$this->naviHref.'?week='.sprintf('%02d',$preWeek).'">Prev</a>'.
                    '<span class="title">'.date('d-m-Y', $this->currentWeek - ($wday - 1)*86400).'</span>'.
                '<a class="next" href="'.$this->naviHref.'?week='.sprintf("%02d", $nextWeek).'">Next</a>'.
            '</div>';
			
    }         
    /**
    * create calendar week labels
    */
	/*
	private function _createLabels(){  
                 
        $content='';
         
        foreach($this->dayLabels as $index=>$label){
             
            $content.='<li class="'.($label==6?'end title':'start title').' title">'.$label.'</li>';
 
        }
         
        return $content;
    }
	*/
    private function _createLabels(){  
                 
        $content='';
        $counter=1; 
		$wday = date('w', $this->currentWeek); 
        foreach($this->dayLabels as $index=>$label){
            $daydate=$label;
			$daydate.=" ";
			$daydate.=date('d/m', $this->currentWeek - ($wday - $counter)*86400); 
            $content.='<li>'.$daydate.'</li>';
			$counter++;
        }
         
        return $content;
    }

     
}