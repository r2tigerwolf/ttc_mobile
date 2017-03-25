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
    
    if(isset($_REQUEST["buslist"])) {
        $route_cache_result = $memcache->get('route');
    
        if($route_cache_result) {
            $route_result = $route_cache_result;
            //echo "<br/>this is cached<br/>";
        } else {
            $rows = '*';
            $table = 'bus_view'; // view
            $join = '';
            $where = '';
            $order = '';
            $limit = '';
            
            $routeResult = $bus->select($busConn, $rows, $table, $join, $where, $order, $limit); 
            
            foreach($routeResult  as $key => $val) {				
                $route_result[$key] = $val;
            }
   
            $memcache->set('route', $route_result, MEMCACHE_COMPRESSED, 100);
            
            //echo "<br/>this is NOT cached<br/>";
        }
    
        $memcache->flush(0);
    

        foreach($route_result  as $key => $val) {
            echo '<li><a href="javascript:void(0);" class="route" route="' . $val['route_id'] . '">' . $val['route_long_name'] . ' ' . $val['route_short_name'] . '</a></li>';  
        }
    

        $memcache->close();
    }

    if(isset($_REQUEST["route"])) {
        $route = $_REQUEST["route"];
        $trips_cache_result = $memcache->get('trips_' . $route); // Memcached object 
        $trips_result = array();
        
        if($trips_cache_result) {
            $trips_result = $trips_cache_result;
            //echo "<br/>this is cached<br/>";
        } else {
            if(isset($_REQUEST["intersection1"])) { 
                $intersection1 = ' and stop_name LIKE "%' . $_REQUEST["intersection1"] . '%"';
            }
            if(isset($_REQUEST["intersection2"])) {
                $intersection2 = ' and stop_name LIKE "%' . $_REQUEST["intersection2"] . '%"';
            }
            
            $rows = '*';
            $table = 'route_view'; // view
            $join = '';
            $where = 'route_id = "' . $route . '"' . $intersection1 . $intersection2;
            $order = '';
            $limit = '';
            
            $tripsResult = $bus->select($busConn, $rows, $table, $join, $where , $order, $limit);
            
            if($tripsResult) {
                foreach($tripsResult  as $key => $val) {				
                    $trips_result[$key] = $val;
                }
                
                // Key, Array, Compressed, seconds
                $memcache->set('trips_' . $route, $trips_result, MEMCACHE_COMPRESSED, 100);
                
                //echo "<br/>this is NOT cached<br/>";
            } else {
                echo "No Result";
            }
        }
        
        //$memcache->flush(0);
        
        foreach($trips_result as $key => $val) {
            echo '<li>';
            echo $val['trip_headsign'] . '<br/> Arrive at: ' . 
            date("g:i A", strtotime($val['arrival_time'])) . ', Depart at: ' . 
            date("g:i A", strtotime($val['departure_time']));
            echo "<br />";
            //echo ', Coordinates: ' . $val['stop_lat'] . ' ' . $val['stop_lon'] . '<br />';
            echo $val['stop_name'];
            echo '</li>';  
        }
 
        $memcache->close(); 
    }
?>