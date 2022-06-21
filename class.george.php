<?php
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


    /**
     * Initialize method for george, check if db with $variationName exist or not, 
     * If exist,
     *  Add +1 visitor, set option with data of the DB [status, discovery_rate, default_view, variation]
     *  If variationName is the view selected stay here
     *  Else redirect to the view selected with Header Location
     * Else
     *  Do Nothing
     * @return void
     */
    function initialize()
    {
        $newURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $newURI = str_replace('index.php', '', $newURI);
        $variationName = trim(str_replace("/", "_",  $newURI), "_"); //Nom variation actuel 
        $variableQuery =  parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ? "?" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) : "";   //Récupération des query

        $data = $this->get_data_custom();

        $this->status = $data['status'];

        if (isset($data['uri']) && ($data['uri'] == $newURI  && !empty($data) || $data != false) && $data['status'] != 1) {
            //SI C'EST EN BDD ALORS ON LANCE LE SCRIPT
            // options

            $this->set_option(
                array(
                    "discovery_rate" => $data['discovery_rate'],
                    "default_view" => $data['default_view'],
                )
            );
            $this->add_variation(
                array(
                    $variationName => array( //Name variation
                        "lp" => "", //Link variation
                    )
                )
            );
            if (empty($variableQuery)) {
                $http_referer = "?http_referer=" . $variationName;
            } else {
                $http_referer = "&http_referer=" . $variationName;
            }
            foreach ($data['listVariation'] as $v) { //On parcours la liste des variations disponible 
                $this->add_variation(
                    array(
                        $v['name'] => array( //Name variation
                            "lp" => $v['uri'] . $variableQuery . $http_referer, //Link variation
                        )
                    )
                );
            }
            $this->calculate(); // On ajoute à la variation actuel
            if ($variationName == $this->selected_view_name) {
                // $this->render('lp');
                return;
            } else {
                if (!headers_sent()) {
                    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $this->render("lp"), false);
                    exit;
                } else {
                    echo '<script>window.location="https://"+window.location.host+"' . $this->render("lp") . '"</script>';
                    exit;
                }
            }
        } else {
            return;
        }
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
            'variation' => $this->selected_view_name,
            'nb_visit' => 1,
            'nb_conversion' => 0,
            'tx_conversion' => 0,
            'nb_conversion_mobile' => 0,
            'nb_conversion_tablet' => 0,
            'nb_conversion_desktop' => 0
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
                $data['tx_conversion'] = round(($data['nb_conversion'] / $data['nb_visit']) * 100, 1);

                if ($this->visit['device_type'] == "mobile") {
                    $data['nb_conversion_mobile'] = max(0, $result[0]['nb_conversion_mobile']) + 1;
                } else if ($this->visit['device_type'] == "tablet") {
                    $data['nb_conversion_tablet'] = max(0, $result[0]['nb_conversion_tablet']) + 1;
                } else {
                    $data['nb_conversion_desktop'] = max(0, $result[0]['nb_conversion_desktop']) + 1;
                }

                $db->table('data_set')->update($result[0]['id'], $data);
            }
        } else {
            return false;
        }
    }


    /**
     * Save conversion with path
     *
     * @param string $path
     * @return void
     */
    function save_conversion_custom($path = "")
    {
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
            $data['tx_conversion'] = max(0, round(($data['nb_conversion'] / $data['nb_visit']) * 100, 1) + 1);
            if ($this->visit['device_type'] == "mobile") {
                $data['nb_conversion_mobile'] = max(0, $result[0]['nb_conversion_mobile']) + 1;
            } else if ($this->visit['device_type'] == "tablet") {
                $data['nb_conversion_tablet'] = max(0, $result[0]['nb_conversion_tablet']) + 1;
            } else {
                $data['nb_conversion_desktop'] = max(0, $result[0]['nb_conversion_desktop']) + 1;
            }
            $db->table('data_set')->update($result[0]['id'], $data);
        }
    }

    function get_data()
    {
        if (file_exists(ABSPATH . LIB . '/George/database/' . $this->test)) {
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
        } else {
            return false;
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

            @$conversion_rate = max(0, round($v['nb_conversion'] / $v['nb_visit'], 1));
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
            // var_dump("TEST");
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


    /**
     * Get list of all DB in directory database
     * @return array
     */
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

    /**
     * Delete DB
     *
     * @param string $nameDB
     * @return void
     */
    function deleteData($nameDB)
    {
        foreach (new DirectoryIterator($nameDB) as $item) {
            if ($item->isFile()) unlink($item->getRealPath());
            if (!$item->isDot() && $item->isDir()) $this->deleteData($item->getRealPath());
        }
        rmdir($nameDB);
    }

    /**
     * Create DB for ABTEST
     *
     * @param string $url_conversion
     * @param string $discovery_rate
     * @param string $urls_variation
     * @return void
     */
    function registerInDB($url_conversion, $discovery_rate, $urls_variation)
    {
        //Create DB for principal
        $db = new FlatDB('database', $this->test);
        $data = array(
            'uri' => $url_conversion,
            'variation' => $this->test,
            'listVariation' => $urls_variation,
            'discovery_rate' => $discovery_rate,
            'default_view' => $this->test,
            'nb_visit' => 0,
            'status' => 0,
            'nb_conversion' => 0,
            'tx_conversion' => 0,
            'nb_conversion_mobile' => 0,
            'nb_conversion_tablet' => 0,
            'nb_conversion_desktop' => 0,
            'date_time' => new \DateTime(),
        );
        $db->table('data_set')->insert(
            $data
        );
        //Create DB for variation
        foreach ($urls_variation as $value => $entry) {
            $data = array(
                'uri' => $entry['uri'],
                'variation' => $entry['name'],
                'nb_visit' => 0,
                'nb_conversion' => 0,
                'tx_conversion' => 0,
                'nb_conversion_mobile' => 0,
                'nb_conversion_tablet' => 0,
                'nb_conversion_desktop' => 0
            );

            $db->table('data_set')->insert(
                $data
            );
        }
        return;
    }
    /**
     * Change State of ABTEST
     *
     * @return void
     */
    function changeStatus()
    {
        $db = new FlatDB('database', $this->test);

        // print_r($data);exit;
        @$result = @$db->table('data_set')->where(
            array(
                'variation' => $this->test,
            )
        )->all();

        $data = $result[0];

        if ($result[0]['status'] == 0) {
            $data['status'] = 1; //Pause
        } else {
            $data['status'] = 0; //Resume
        }

        $db->table('data_set')->update($result[0]['id'], $data);
    }

    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }

    /**
     * Archive or Not ABTEST
     */
    function setArchive()
    {
        $db = new FlatDB('database', $this->test);

        // print_r($data);exit;
        @$result = @$db->table('data_set')->where(
            array(
                'variation' => $this->test,
            )
        )->all();

        $data = $result[0];

        if ($this->str_contains($data['uri'], "#archived")) {
            $data['uri'] = str_replace("#archived", "", $data['uri']);
        } else {
            $data['uri'] = '#archived' . $data['uri'];
        }


        $db->table('data_set')->update($result[0]['id'], $data);
    }

    /**
     * Get data from master-header
     *
     * @return void
     */
    function get_data_custom()
    {
        if (file_exists(ABSPATH . LIB . '/George/database/' . $this->test)) {
            // $db = new FlatDB('database', $this->test);
            $db = new FlatDB(ABSPATH . LIB . '/George/database', $this->test);

            $result = $db->table('data_set')->all();
            // exit;
            if (empty($result[0])) {
                return "false";
            } else {
                return $result[0];
            }
        } else {
            return false;
        }
    }
    /**
     * Get data for one ABTEST
     */
    function get_data_by_abtest()
    {
        if (file_exists('database/' . $this->test)) {
            // $db = new FlatDB('database', $this->test);
            $db = new FlatDB('database', $this->test);

            $result = $db->table('data_set')->all();
            // exit;
            if (empty($result[0])) {
                return "false";
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }

    function get_data_custom_for_conversion($nameDatabase)
    {
        if (file_exists('database/' . $nameDatabase)) {
            $db = new FlatDB('database', $nameDatabase);
            @$result = @$db->table('data_set')->where(
                array(
                    'variation' => $nameDatabase,
                )
            )->all();

            // exit;
            if (empty($result)) {
                return "false";
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }

    /**
     * Display all DB
     *
     * @return string
     */
    function draw_allData()
    {
        $allDb = $this->show_allData();
        $play = '<div class="tab-pane fade show active " id="pills-play" role="tabpanel" aria-labelledby="pills-play-tab"><div class="listDB">';
        $pause = '<div class="tab-pane fade " id="pills-pause" role="tabpanel" aria-labelledby="pills-pause-tab"><div class="listDB">';
        $archived = '<div class="tab-pane fade " id="pills-archived" role="tabpanel" aria-labelledby="pills-archived-tab"><div class="listDB">';


        foreach ($allDb as $oneDB) {
            $t = "";
            if ($this->str_contains($oneDB[0]['uri'], "#archived")) {
                $t .= '<div class="card card-archived">';
            } else {
                $t .= '<div class="card">';
            }
            $t .=   '<div class="cadre-text p-3 row">
                        <div class="col-12 col-md-4 mt-2">
                            <p><u>Date</u> : <b>' . $oneDB[0]['date_time']->format('d/m/Y H:i') . '</b></p>';
            if ($oneDB[0]['status'] == 0) {
                $t .=           "<p><u>Status</u> : <b class='text-primary'>En cours</b></p>";
            } else {
                $t .=           "<p><u>Status</u> : <b class='text-warning'>En pause</b></p>";
            }
            $t .=           '<p><u>Discovery Rate</u> : <b>' . $oneDB[0]['discovery_rate'] . '</b></p>';
            $t .=           "<div class='d-flex justify-content-evenly'>";
            $t .=               "<a class='btn btn-outline-danger' href='switchGeorge.php?action=delete&db=" . $oneDB[0]['variation'] . "'>Delete</a>";
            if ($oneDB[0]['status'] == 0) {
                $t .=               "<a class='btn btn-outline-warning ml-3' href='switchGeorge.php?action=changeState&db=" . $oneDB[0]['variation'] . "'>Pause</a>";
            } else {
                $t .=               "<a class='btn btn-outline-primary ml-3' href='switchGeorge.php?action=changeState&db=" . $oneDB[0]['variation'] . "'>Reprendre</a>";
            }
            $t .=           '</div>';
            $t .=       '</div>';
            $t .=   '<div class="col-12 col-md-8">';
            foreach ($oneDB as $index => $entry) {
                $t .=   '<div class="col-12 mt-2">';
                $t .=       '<p><u>Variation</u> : ' . trim($entry['uri'], "/") . '</p>';
                $t .=       '<div class="row justify-content-center text-center mx-auto">';
                $t .=           '<div class="col-6 col-md-4">
                                    <div class="roundedCardText mx-auto">
                                        <div>V</div>
                                        <div><b>(' . $entry['nb_visit'] . ')</b></div>
                                    </div>
                                </div>';
                $t .=           '<div class="col-6 col-md-4">
                                    <div class="roundedCardText mx-auto">
                                        <div>C</div>
                                        <div><b>(' . $entry['nb_conversion'] . ')</b></div>
                                    </div>
                                </div>';

                $t .=           '<div class="col-6 col-md-4">
                                    <div class="roundedCardText mx-auto">
                                        <div>TX</div>
                                        <div><b>(' . $entry['tx_conversion'] . '%)</b></div>
                                    </div>
                                </div>';
                $t .=           '</div>';
                $t .=       '</div>';
            }
            $t .= '</div>';
            if ($oneDB[0]['status'] == 0) {
                $t .= '</div><div class="bottomBar bg-primary text-white text-center"><a href="page_abtest.php?dbName=' . $oneDB[0]['variation'] . '"><h5>' . trim($oneDB[0]['uri'], '/')  . '</h5></a></div>';
            } else {
                $t .= '</div><div class="bottomBar bg-secondary text-white text-center"><a href="page_abtest.php?dbName=' . $oneDB[0]['variation'] . '"><h5>' . trim($oneDB[0]['uri'], '/')  . '</h5></a></div>';
            }
            $t .= "</div>";

            if ($oneDB[0]['status'] == 0 && !$this->str_contains($oneDB[0]['uri'], "#archived")) {
                $play .= $t;
            }

            if ($oneDB[0]['status'] == 1 && !$this->str_contains($oneDB[0]['uri'], "#archived")) {
                $pause .= $t;
            }

            if ($this->str_contains($oneDB[0]['uri'], "#archived")) {
                $archived .= $t;
            }
        }

        $play .= '</div></div>';
        $pause .= '</div></div>';
        $archived .= '</div></div>';

        $display = $play . $pause . $archived;

        return $display;
    }

    /**
     * Sort array multidimentional
     * @param array $array
     * @param string $cols
     * @return array
     */
    function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_' . $k] = strtolower($row[$col]);
            }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
        }
        $eval = substr($eval, 0, -1) . ');';
        eval($eval);
        $ret = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k, 1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;
    }

    /**
     * Display data for one ABTest
     * @return string
     */
    function draw_abtest()
    {
        $abtest = $this->get_data_by_abtest();
        $state = $abtest[0]['status'] == 0 ? "En cours" : "En pause";
        $draw = '
            <div class="headerCard">';
        if ($this->str_contains($abtest[0]['uri'], "#archived")) {
            $draw .= '<h3 class="text-info text-center">ABTEST Archivé</h3>';
            $state = 'Archivé';
        }
        $draw .= '
                <div class="date_crea text-center">Date de création : ' . $abtest[0]['date_time']->format('d/m/Y H:i') . '</div>
                <div class="discovery_rate text-center d-flex align-items-center justify-content-between">
                    <p><span class="text-info">' . $state . '</span> | Taux de découverte : ' . $abtest[0]['discovery_rate'] * 100 . '% </p>
                    <div class="dropdown">
                        <p class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </p>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item text-info" href="switchGeorge.php?action=changeState&db=' . $abtest[0]['variation'] . '">Pause/Play</a>
                            <a class="dropdown-item text-warning" href="switchGeorge.php?action=setArchive&db=' . $abtest[0]['variation'] . '">Archiver</a>
                            <a class="dropdown-item text-danger" href="switchGeorge.php?action=delete&db=' . $abtest[0]['variation'] . '">/!\ Supprimer</a>
                        </div>
                    </div>
                </div>
            </div>
        ';

        $draw .= '<div class="d-flex align-items-center justify-content-center flex-wrap">';

        $abtest = $this->array_msort($abtest, array('tx_conversion' => SORT_DESC, 'nb_visit' => SORT_DESC)); //Permet de trier un tableau multidimensionnel par ordre décroissant

        $i = 0;
        foreach ($abtest as $key => $value) {

            $draw .= '<div class="card">';
            $draw .= '<h2 class="text-center">' . $value['variation'] . '</h2>';
            $draw .= '<div class="row justify-content-center align-items-center">';
            $draw .= '<div class="col-6">
                            <h6 class="mt-5 text-center">Nombre de visiteur</h6>
                            <div class="roundedCardText mx-auto">
                                <div><b>' . $value['nb_visit'] . '</b></div>
                            </div>
                        </div>';
            $draw .= '<div class="col-6">
                    <h6 class="mt-5 text-center">Convertion mobile</h6>
                    <div class="roundedCardText mx-auto">
                        <div><b>' .  round(($value['nb_conversion_mobile'] / $value['nb_visit']) * 100, 1) . '%</b></div>
                    </div>
                </div>';

            $draw .= '<div class="col-6">
                        <h6 class="mt-5 text-center">Conversion tablette</h6>
                        <div class="roundedCardText mx-auto">
                            <div><b>' . round(($value['nb_conversion_tablet'] / $value['nb_visit']) * 100, 1) . '%</b></div>
                        </div>
                    </div>';

            $draw .= '<div class="col-6">
                    <h6 class="mt-5 text-center">Conversion PC</h6>
                    <div class="roundedCardText mx-auto">
                        <div><b>' . round(($value['nb_conversion_desktop'] / $value['nb_visit']) * 100, 1) . '%</b></div>
                    </div>
                </div>';

            $draw .= '<div class="col-6">
                <h6 class="mt-5 text-center">Conversion total</h6>
                <div class="roundedCardText mx-auto">
                    <div><b>' . $value['nb_conversion'] . '</b></div>
                </div>
            </div>';

            $draw .= '<div class="col-6">
                    <h6 class="mt-5 text-center">Taux de conversion</h6>';
            if ($i == 0) { //On repère le premier et le plus performant
                $draw .= '<div class="roundedCardText text-white bg-primary mx-auto">';
            } else {
                $draw .= '<div class="roundedCardText text-white bg-secondary mx-auto">';
            }
            $draw .= '<div><b>' . $value['tx_conversion'] . '%</b></div>
                    </div>
                </div>';
            $draw .= '</div>';
            $draw .= '</div>';

            $i++;
        }

        $draw .= "</div>";

        return $draw;
    }
}
