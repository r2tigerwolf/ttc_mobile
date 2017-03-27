<?php  
    include("db.class.php");
    $memcache = new Memcache();
    $memcache->connect('localhost', 11211) or die ("Could not connect");

    $conn = new Connect();   
    $busConn = $conn->dbconnect();
    $bus = new Bus; 
       
    $route_cache_result = array();
    $trips_cache_result = array();
    
    $memcache->set('key', '', MEMCACHE_COMPRESSED, 100);
    
    foreach($_REQUEST as $key => $val) {
        ${$key} = $val;
    }
    
    if(isset($buslist)) {
        $route_cache_result = $memcache->get('route');
    
        if($route_cache_result) {
            $routeResult = $route_cache_result;
            //echo "<br/>this is cached<br/>";
        } else {
            $sqlArray = array('conn' => $busConn, 'rows' => '*', 'table' => 'bus_view', 'join' => '', 'where' => '', 'order' => '', 'limit' => '');
            $routeResult = $bus->select($sqlArray); 
            $memcache->set('route', $routeResult, MEMCACHE_COMPRESSED, 0);
            //echo "<br/>this is NOT cached<br/>";
        }
    
        $memcache->flush(0);
    
        foreach($routeResult  as $key => $val) {
            echo '<li><a href="javascript:void(0);" class="route" route="' . $val['route_id'] . '">' . $val['route_long_name'] . ' ' . $val['route_short_name'] . '</a></li>';  
        }

        $memcache->close();
    }

    if(isset($route)) {
        $trips_cache_result = $memcache->get('trips_' . $route . '_' . $intersection1 . '_' . $intersection2); // Memcached object 
        $trips_result = array();
        
        if($trips_cache_result) {
            $trips_result = $trips_cache_result;
            echo "this is cached<br/>";
        } else {
            if(isset($intersection1)) { 
                $intersection1 = ' and stop_name LIKE "%' . $intersection1 . '%"';
            }
            
            if(isset($intersection2)) {
                $intersection2 = ' and stop_name LIKE "%' . $intersection2 . '%"';
            }
            
            $sqlArray = array('conn' => $busConn, 'rows' => '*', 'table' => 'route_view', 'join' => '', 'where' => 'route_id = "' . $route . '"' . $intersection1 . $intersection2, 'order' => '', 'limit' => '');
            $tripsResult = $bus->select($sqlArray); 
            
            if($tripsResult) {
                $memcache->set('trips_' . $route . '_' . $_REQUEST["intersection1"] . '_' . $_REQUEST["intersection2"], $tripsResult, MEMCACHE_COMPRESSED, 0);
                echo "this is NOT cached<br/>";
            } else {
                echo "No Result";
            }
        }
        
        $memcache->flush(0);
        
        foreach($tripsResult as $key => $val) {
            echo '<li>';
            echo $val['trip_headsign'] . '<br/> Arrive at: ' . 
            date("g:i A", strtotime($val['arrival_time'])) . ', Depart at: ' . 
            date("g:i A", strtotime($val['departure_time']));
            echo "<br />";
            echo $val['stop_name'];
            echo '</li>';  
        }
 
        $memcache->close(); 
    }
?>