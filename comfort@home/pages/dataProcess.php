<?
    function getValue($link, $node_id, $sensor_name) {
        $sql = "select * from data where node_id = '$node_id' and sensor_name = '$sensor_name' order by context_id desc limit 1";
        $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
        $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
        echo intval($row["value"])." ";
        mysql_free_result($result);
    }
    function getTimeline($link, $node_id, $querytime, $type, $timeStart, $timeEnd) {
        $value = array();
        $querytime = $querytime * 60;
        $sql = "select * from data where node_id='$node_id' and sensor_name = '$type' and timestamp between '$timeStart' and '$timeEnd' order by context_id";
        $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
        $timebase = strtotime($timeStart.' 00:00:00');
        $i = 0;
        $sum = 0;
        $counter = 0;
        for ($j=0; $j<mysql_num_rows($result); $j++) {
            $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
            $time = strtotime($row["timestamp"])-$timebase;
            $sum     = $sum + $row["value"];
            $counter = $counter + 1;
            if(floor($time/$querytime) == $i+1) {
                $sum = $sum/$counter;
                array_push($value,$sum);
                $sum     = $row["value"];
                $counter = 1;
                $i += 1;
            }
            for($k=1;$k<=floor($time/$querytime)-$i;$k++)
                array_push($value,null);
            $i = floor($time/$querytime);
        }
        if($counter!=0) {
            $sum = $sum/$counter;
            array_push($value,$sum);
        }
        mysql_free_result($result);
        
        return $value;
    }
    function xmlWrite($fileName, $color, $node_id, $value, $unit, $section, $timeStart, $timeEnd) {
        $fp = fopen($fileName, 'w');
        
        // header
        fwrite($fp, "<chart>\r\n");
        fwrite($fp, "    <chart_border top_thickness='2' bottom_thickness='2' left_thickness='2' right_thickness='2' />\r\n");
        
        // data start
        fwrite($fp, "    <chart_data>\r\n");

        // horizon : time
        fwrite($fp, "        <row>\r\n");
        fwrite($fp, "           <null/>\r\n");
        for($i=0; $i<$section; $i++) {
            if($i%4==0) {
                $temp = $i/($section/24);
                fwrite($fp, "           <string>$temp</string>\r\n");
            }
            else        fwrite($fp, "           <string></string>\r\n");
        }
        fwrite($fp, "        </row>\r\n");
        
        // vertical : data
        for($i=1; $i<=1; $i++) {
            fwrite($fp, "        <row>\r\n");
            fwrite($fp, "           <string>$node_id</string>\r\n");
            for($k=0; $k<$section; $k++) {
                if($value[$k])
                    fwrite($fp, "           <number>".$value[$k]."</number>\r\n");
                else
                    fwrite($fp, "           <null/>\r\n");
            }
            fwrite($fp, "        </row>\r\n");
        }
        
        // data end
        fwrite($fp, "    </chart_data>\r\n");
        
        // type
        fwrite($fp, "    <chart_grid_h alpha='10' thickness='1' type='solid' />\r\n");
        fwrite($fp, "    <chart_grid_v alpha='10' thickness='1' type='solid' />\r\n");
        fwrite($fp, "    <chart_guide horizontal='true' thickness='1' color='ffffff' alpha='50' type='dashed' radius='5' 
                                      line_color='000000' line_alpha='75' line_thickness='2' text_color='000000' text_h_alpha='90' />\r\n");
        fwrite($fp, "    <chart_type>line</chart_type>\r\n");
        fwrite($fp, "    <chart_pref point_shape='circle' line_thickness='2' zero_line='false' />\r\n");
        fwrite($fp, "    <chart_rect x='70' y='45' />\r\n");
        
        // draw
        fwrite($fp, "    <draw>\r\n");
        fwrite($fp, "        <text shadow='low'  color='808080' alpha='55' size='18' x='220' y='270'  width='200' height='200' h_align='left'>Hour</text>\r\n");
        fwrite($fp, "        <text shadow='low'  color='808080' alpha='55' size='18' x='10'  y='200'  width='200' height='200' h_align='left' rotation='-90'>$unit</text>\r\n");
        fwrite($fp, "        <text shadow='high' color='$color' alpha='85' size='14' x='40'  y='10'   width='200' height='200' h_align='left'>$timeStart</text>\r\n");
        fwrite($fp, "        <text shadow='high' color='$color' alpha='85' size='14' x='380'  y='10'   width='200' height='200' h_align='left'>$timeEnd</text>\r\n");
        fwrite($fp, "    </draw>\r\n");
        
        // color
        fwrite($fp, "    <series_color>\r\n");
        fwrite($fp, "        <color>$color</color>\r\n");
        fwrite($fp, "    </series_color>\r\n");
        
        // postscript
        fwrite($fp, "</chart>\r\n");
        
        fclose($fp);
    }
?>
