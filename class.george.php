<?php

use LDAP\Result;

include_once './library/Mobile-Detect-2.8.25/Mobile_Detect.php';
include_once './library/class.browser.php';
include_once './library/FlatDB/flatdb.php';


class george
{
    public function __construct($name = "test_data", $tracking_var = array())
    {

        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
        }

        $this->test = $name;
        $this->set_visit_data();
    }

    function set_tracking_var($tracking_var, $VAR)
    {
        $this->data = $VAR;
        $this->tracking_var = $tracking_var;
        // $this->get_tracking_var($VAR);
    }

    function set_option($options_array)
    {
        foreach ($options_array as $k => $d) {
            @$this->option[$k] = $d;
        }
    }

    function set_filter($filters_array)
    {
        foreach ($filters_array as $k => $d) {
            @$this->filter[$k] = $d;
        }
    }


    function check_filters()
    {
        if (!empty($this->filters['device_type']) and $this->filters['device_type'] != $this->visit['device_type']) {
            return false;
        }
        if (!empty($this->filters['browser']) and $this->filters['browser'] != $this->visit['browser']) {
            return false;
        }
        if (!empty($this->filters['plateform']) and $this->filters['plateform'] != $this->visit['plateform']) {
            return false;
        }
        if (!empty($this->filters['utm_source']) and $this->filters['utm_source'] != $this->visit['utm_source']) {
            return false;
        }
        if (!empty($this->filters['utm_content']) and $this->filters['utm_content'] != $this->visit['utm_content']) {
            return false;
        }
        if (!empty($this->filters['utm_campaign']) and $this->filters['utm_campaign'] != $this->visit['utm_campaign']) {
            return false;
        }
        if (!empty($this->filters['utm_term']) and $this->filters['utm_term'] != $this->visit['utm_term']) {
            return false;
        }

        return true;
    }


    function set_visit_data()
    {

        //DETECT DEVICE / BROWSER / OS / LANGUAGE
        $detect = new Mobile_Detect();
        $browser = new browser();
        $this->visit['device_type'] = strtolower(($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'computer'));
        $this->visit['browser'] = strtolower($browser->getBrowser());
        $this->visit['plateform'] = strtolower($browser->getPlatform());
        $this->visit['ip'] = $this->get_ip();
        $this->visit['lang'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $this->visit['timestamp'] = time();
        @$this->visit['uri'] = str_replace("index.php", "", strtolower(strtok($_SERVER['REQUEST_URI'], '?')));
        @$this->visit['utm_source'] = strtolower($_REQUEST['utm_source']);
        @$this->visit['utm_content'] = strtolower($_REQUEST['utm_content']);
        @$this->visit['utm_campaign'] = strtolower($_REQUEST['utm_campaign']);
        @$this->visit['utm_term'] = strtolower($_REQUEST['utm_term']);

        return;
    }

    function save_visit()
    {
        $db = new FlatDB(ABSPATH . LIB . "/George/database", $this->test);

        $data = array(
            'uri' => "/" . str_replace("_", "/", $this->selected_view_name) . "/",
            @$this->option['tracking_var'] => @$_REQUEST[$this->option['tracking_var']],
            'variation' => $this->selected_view_name,
            'nb_visit' => 1,
            'nb_conversion' => 0,
        );

        // print_r($data);exit;
        @$result = @$db->table('data_set')->where(
            array(
                'uri' => "/" . str_replace("_", "/", $this->selected_view_name) . "/",
                $this->option['tracking_var'] => $_REQUEST[$this->option['tracking_var']],
                'variation' => $this->selected_view_name
            )
        )->all();


        if (!empty($result[0]['id'])) {
            $data = $result[0];
            $data['nb_visit'] = max(0, $result[0]['nb_visit']) + 1;
            $db->table('data_set')->update($result[0]['id'], $data);
        } else {
            $result = $db->table('data_set')->insert(
                $data
            );
        }
    }

    function save_conversion()
    {
        if (!empty($_SESSION['URI']) && !empty($_SESSION['VARIATION']) && !empty($_SESSION['TEST'])) {
            $data = array(
                'uri' => strtolower($_SESSION['URI']),
                'variation' => $_SESSION['VARIATION']
            );
            foreach ($_SESSION['VAR'] as $k => $v) {
                $data[$k] = $v;
            }


            $db = new FlatDB(ABSPATH . LIB . "/George/database", $_SESSION['TEST']);
            // print_r($data);exit;
            @$result = @$db->table('data_set')->where(
                $data
            )->all();

            if (!empty($result[0]['id'])) {
                $data = $result[0];
                $data['nb_conversion'] = max(0, $result[0]['nb_conversion']) + 1;

                $db->table('data_set')->update($result[0]['id'], $data);
            }
        } else {
            return false;
        }
    }

    function save_conversion_custom($path)
    {
        if (empty($path)) {
            if (!empty($_SESSION['URI']) && !empty($_SESSION['VARIATION']) && !empty($_SESSION['TEST'])) {
                $data = array(
                    'uri' => strtolower($_SESSION['URI']),
                    'variation' => $_SESSION['VARIATION']
                );
                foreach ($_SESSION['VAR'] as $k => $v) {
                    $data[$k] = $v;
                }


                $db = new FlatDB("database", $_SESSION['TEST']);
                // print_r($data);exit;
                @$result = @$db->table('data_set')->where(
                    $data
                )->all();

                if (!empty($result[0]['id'])) {
                    $data = $result[0];
                    $data['nb_conversion'] = max(0, $result[0]['nb_conversion']) + 1;

                    $db->table('data_set')->update($result[0]['id'], $data);
                }
            }
        } else {
            $data = array(
                'uri' => $path,
                'variation' => trim(str_replace("/", "_", $path), "_")
            );
            foreach ($_SESSION['VAR'] as $k => $v) {
                $data[$k] = $v;
            }


            $db = new FlatDB("database", $this->test);
            // print_r($data);exit;
            @$result = @$db->table('data_set')->where(
                $data
            )->all();

            if (!empty($result[0]['id'])) {
                $data = $result[0];
                $data['nb_conversion'] = max(0, $result[0]['nb_conversion']) + 1;
                $db->table('data_set')->update($result[0]['id'], $data);
            }
        }
    }

    function get_data()
    {
        $db = new FlatDB(ABSPATH . LIB . '/George/database', $this->test);

        @$result = @$db->table('data_set')->where(
            array(
                'uri' => $this->visit['uri'],
                // $this->option['tracking_var'] => $_REQUEST[$this->option['tracking_var']]
            )
        )->all();
        // exit;

        if (empty($result)) {
            return false;
        } else {
            return $result;
        }
    }

    function calculate_conversion($data)
    {
        $this->nb_visit = 0;
        $max_conversion_rate = 0;
        foreach ($data as $k => $v) {

            if (empty($this->variation[$v['variation']])) {
                unset($data[$k]);
                continue;
            }

            @$conversion_rate = max(0, round($v['nb_conversion'] / $v['nb_visit'], 2));
            $data_index[] = $conversion_rate;
            $max_conversion_rate = max($conversion_rate, $max_conversion_rate);
            @$this->nb_visit = $this->nb_visit + max(1, $v['nb_visit']);
        }
        if (empty($data)) {
            return false;
        }

        $this->nb_visit = max(1, $this->nb_visit);
        array_multisort($data_index, SORT_DESC, $data);
        if (!$max_conversion_rate > 0) {
            shuffle($data);
        }
        return $data;
    }




    function calculate()
    {
        global $_REQUEST;
        $this->selected_view = @$this->variation[$this->option['default_view']];
        $this->selected_view_name = @$this->option['default_view'];

        if (!empty($this->filter['utm_source']) and $this->filter['utm_source'] != $this->visit['utm_source']) {
            return false;
        }

        // select view
        // get tracking data
        $data = $this->get_data();


        if ($data && $data !== false) {
            $data = $this->calculate_conversion($data);

            if (empty($data) or $data === false) {
                $best_view_name = $this->option['default_view'];
            } else {
                $best_view = array_shift($data);
                $best_view_name = $best_view['variation'];
            }

            $key2 = ($this->nb_visit + 1) * (max(0.01, $this->option['discovery_rate']));
            $key1 = ($this->nb_visit) * (max(0.01, $this->option['discovery_rate']));

            if (floor($key2) - floor($key1) == 1) {
                // explore
                // echo 'explore';
                // unset($this->variation[$best_view_name]);
                $this->selected_view_name = array_rand($this->variation);
                $this->selected_view = $this->variation[$this->selected_view_name];
            } else {
                // echo 'exploit';
                $this->selected_view = $this->variation[$best_view_name];
                $this->selected_view_name = $best_view_name;
                // echo ' '.$best_view_name;					
            }
        }


        $this->save_visit();
        $_SESSION['IP'] = $this->get_ip();
        $_SESSION['URI'] = $best_view['uri'];
        @$_SESSION['VAR'] = array($this->option['tracking_var'] => $_REQUEST[$this->option['tracking_var']]);
        $_SESSION['VARIATION'] = $this->selected_view_name;
        $_SESSION['TEST'] = $this->test;
        $_REQUEST['utm_campaign'] = @trim("-", $this->test . "-" . $this->selected_view_name . "-" . $_REQUEST['utm_campaign']);
    }

    function get_ip()
    {
        // IP si internet partag�
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // IP derri�re un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Sinon : IP normale
        else {
            return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        }
    }

    function add_variation($array)
    {
        foreach ($array as $k => $v) {
            $this->variation[$k] = $v;
        }
    }

    function render($k)
    {
        return $this->selected_view[strtolower($k)];
    }



    function show_allData()
    {
        $dirs = scandir("database"); //On récupère le nom de toutes les DB disponibles
        unset($dirs[array_search(".", $dirs)]); //On Supprime les deux premiers éléments du tableau
        unset($dirs[array_search("..", $dirs)]); //On Supprime les deux premiers éléments du tableau

        $ListDB = [];


        foreach ($dirs as $db) {
            @$db = new FlatDB('database', $db);
            @$result = @$db->table('data_set')->all();

            if ($result !== false) {
                array_push($ListDB, $result);
            }
        }
        return $ListDB;
    }

    function deleteData($nameDB)
    {
        foreach (new DirectoryIterator($nameDB) as $item) {
            if ($item->isFile()) unlink($item->getRealPath());
            if (!$item->isDot() && $item->isDir()) $this->deleteData($item->getRealPath());
        }
        rmdir($nameDB);
    }


    function registerInDB($url_conversion, $discovery_rate, $urls_variation)
    {

        $db = new FlatDB('database', $this->test);
        $data = array(
            'uri' => $url_conversion,
            @$this->option['tracking_var'] => @$_REQUEST[$this->option['tracking_var']],
            'variation' => $this->test,
            'listVariation' => $urls_variation,
            'discovery_rate' => $discovery_rate,
            'default_view' => $this->test,
            'nb_visit' => 0,
            'nb_conversion' => 0,
            'date_time' => new \DateTime(),
        );

        $result = $db->table('data_set')->insert(
            $data
        );

        return;
    }

    function get_data_custom()
    {
        // $db = new FlatDB('database', $this->test);
        $db = new FlatDB(ABSPATH . LIB . '/George/database', $this->test);

        $result = $db->table('data_set')->all();
        // exit;
        if (empty($result[0])) {
            return "false";
        } else {
            return $result[0];
        }
    }

    function get_data_custom_for_conversion($nameDatabase = "")
    {

        $db = new FlatDB('database', $nameDatabase ? $nameDatabase : $this->test);
        //$db = new FlatDB(ABSPATH . LIB . '/George/database', $this->test);


        @$result = @$db->table('data_set')->where(
            array(
                'uri' => $this->test,
                $this->option['tracking_var'] => $_REQUEST[$this->option['tracking_var']]
            )
        )->all();

        // exit;
        if (empty($result[0])) {
            return "false";
        } else {
            return $result[0];
        }
    }

    function draw_allData()
    {
        $allDb = $this->show_allData();

        $t = "";

        foreach ($allDb as $oneDB) {
            $nameABtest = "";
            $t .= '<div class="card"><div class="cadre-text p-3">';
            $t .= "<p class='text-center'><u>Date</u> : <b>" . $oneDB[0]['date_time']->format('d/m/Y H:i') . "</b></p>";
            $t .= "<div class='d-flex justify-content-between'><p><u>Discovery Rate</u> : <b>" . $oneDB[0]['discovery_rate'] . "</b></p><a class='btn btn-outline-danger' href='delDB.php?db=" . $oneDB[0]['variation'] . "'>Delete</a></div>";
            foreach ($oneDB as $index => $entry) {
                $nameABtest .= trim($entry['uri'], "/") . ' & ';
                $t .= '<div class="col-12 mx-auto mt-2 mb-2">';
                $t .= '<p><u>Variation</u> : ' . trim($entry['uri'], "/") . '</p>';
                $t .= '<div class="row justify-content-center text-center mx-auto">';
                $t .= '<div class="col-4">
                        <div class="roundedCardText mx-auto">
                            <div>V</div>
                            <div><b>(' . $entry['nb_visit'] . ')</b></div>
                        </div>
                    </div>';
                $t .= '<div class="col-4">
                        <div class="roundedCardText mx-auto">
                            <div>C</div>
                            <div><b>(' . $entry['nb_conversion'] . ')</b></div>
                        </div>
                    </div>';
                if ($entry['nb_visit'] > 0) {
                    $t .= '<div class="col-4">
                    <div class="roundedCardText mx-auto">
                        <div>TX</div>
                        <div><b>(' . round(($entry['nb_conversion'] / $entry['nb_visit']) * 100, 1) . '%)</b></div>
                    </div>
                </div>';
                }
                $t .= '</div>';
                $t .= '</div>';
            }
            $t .= '</div><div class="bottomBar bg-primary text-white text-center"><h5>' . trim($nameABtest, ' & ') . '</h5></div>';
            $t .= "</div>";
        }

        return $t;
    }
}
