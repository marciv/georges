<?php
include_once './library/Mobile-Detect-2.8.25/Mobile_Detect.php';
include_once './library/class.browser.php';
include_once './library/FlatDB/flatdb.php';
class george
{
    /**
     *
     * @var int
     * 
     */
    public $nb_visit;
    /**
     *
     * @var int
     */
    public $status;
    /**
     *
     * @var array<string>
     */
    public $data;
    /**
     *
     * @var string
     */
    public $test;
    /**
     *
     * @var string
     */
    public $selected_view;
    /**
     *
     * @var string
     */
    public $selected_view_name;
    /**
     *
     * @var array<string>
     */
    public $variation;

    /**
     *
     * @var array<string>
     */
    public $option;
    // /**
    //  *
    //  * @var array<string>
    //  */
    // private $filter;
    /**
     *
     * @var array<mixed>
     */
    public $visit;
    // /**
    //  *
    //  * @var array<string>
    //  */
    // private $tracking_var;


    /**
     * Constructor
     *
     * @param string $name
     * @param array<string> $tracking_var
     */
    public function __construct(string $name = "test_data", array $tracking_var = array())
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
     *
     * @return void
     */
    public function initialize(): void
    {
        $newURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $newURI = str_replace('index.php', '', $newURI);
        $variationName = trim(str_replace("/", "_",  $newURI), "_"); //Nom variation actuel 
        $variableQuery =  parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ? "?" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) : "";   //Récupération des query

        $data = $this->get_data_all();
        $data = $data[0];

        if (isset($data['uri']) && ($data['uri'] == $newURI  && !empty($data) || $data != false) && $data['status'] != 1) {
            //SI C'EST EN BDD ALORS ON LANCE LE SCRIPT
            // options
            $this->set_option( //Set Option
                array(
                    "discovery_rate" => $data['discovery_rate'],
                    "default_view" => $data['default_view'],
                )
            );
            $this->add_variation( //Set this variation as selected
                array(
                    $variationName => array( //Name variation
                        "uri" => "", //Link variation
                    )
                )
            );
            if (empty($variableQuery)) {
                $http_referer = "?http_referer=" . $variationName;
            } else {
                $http_referer = "&http_referer=" . $variationName;
            }
            foreach ($data['listVariation'] as $v) { //On parcours la liste des variations disponible 
                $this->add_variation( //Set variation in this list
                    array(
                        $v['name'] => array( //Name variation
                            "uri" => $v['uri'] .  $variableQuery, //Link variation
                        )
                    )
                );
            }
            $this->calculate(); // On ajoute à la variation actuel
            if ($variationName != $this->selected_view_name) {
                if (!headers_sent()) {
                    $hostURL = (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
                    $requestScheme = "https";
                    if ($hostURL == "localhost") {
                        $requestScheme = "http";
                    }
                    header('Location: ' . $requestScheme . '://' . $hostURL . $this->render("uri") . $http_referer, false);
                    exit;
                } else {
                    echo '<script>window.location="https://"+window.location.host+"' . $this->render("uri") . $http_referer . '"</script>';
                    exit;
                }
            }
        }
    }

    // /**
    //  * Set tracking var
    //  *
    //  * @param array<string> $tracking_var
    //  * @param array<string> $VAR
    //  * @return void
    //  */
    // private function set_tracking_var(array $tracking_var, array $VAR): void
    // {
    //     $this->data = $VAR;
    //     $this->tracking_var = $tracking_var;
    //     // $this->get_tracking_var($VAR);
    // }

    /**
     * Set Option
     *
     * @param array<string> $options_array
     * @return void
     */
    private function set_option(array $options_array): void
    {
        foreach ($options_array as $k => $d) {
            @$this->option[$k] = $d;
        }
    }

    // /**
    //  * Set Filter
    //  *
    //  * @param array<string> $filters_array
    //  * @return void
    //  */
    // private function set_filter(array $filters_array): void
    // {
    //     foreach ($filters_array as $k => $d) {
    //         @$this->filter[$k] = $d;
    //     }
    // }


    // /**
    //  * Check filter
    //  *
    //  * @return boolean
    //  */
    // private function check_filters(): bool
    // {
    //     if (!empty($this->filters['device_type']) and $this->filters['device_type'] != $this->visit['device_type']) {
    //         return false;
    //     }
    //     if (!empty($this->filters['browser']) and $this->filters['browser'] != $this->visit['browser']) {
    //         return false;
    //     }
    //     if (!empty($this->filters['plateform']) and $this->filters['plateform'] != $this->visit['plateform']) {
    //         return false;
    //     }
    //     if (!empty($this->filters['utm_source']) and $this->filters['utm_source'] != $this->visit['utm_source']) {
    //         return false;
    //     }
    //     if (!empty($this->filters['utm_content']) and $this->filters['utm_content'] != $this->visit['utm_content']) {
    //         return false;
    //     }
    //     if (!empty($this->filters['utm_campaign']) and $this->filters['utm_campaign'] != $this->visit['utm_campaign']) {
    //         return false;
    //     }
    //     if (!empty($this->filters['utm_term']) and $this->filters['utm_term'] != $this->visit['utm_term']) {
    //         return false;
    //     }

    //     return true;
    // }


    /**
     * set Data visit
     *
     * @return void
     */
    private function set_visit_data(): void
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

    /**
     * Save visit
     *
     * @return void
     */
    private function save_visit(): void
    {
        $db = new FlatDB(ABSPATH . LIB . "/George/database", $this->test);

        $data = array(
            'uri' => "/" . str_replace("_", "/", $this->selected_view_name) . "/",
            'variation' => $this->selected_view_name,
            'nb_visit' => 1,
            'nb_visit_mobile' => $this->visit['device_type'] == "mobile" ? 1 : 0,
            'nb_visit_desktop' => $this->visit['device_type'] == "computer" ? 1 : 0,
            'nb_visit_tablet' => $this->visit['device_type'] == "tablet" ? 1 : 0,
            'nb_conversion' => 0,
            'tx_conversion' =>  0,
            'nb_conversion_mobile' => 0,
            'nb_conversion_tablet' => 0,
            'nb_conversion_desktop' => 0
        );

        // print_r($data);exit;
        @$result = @$db->table('data_set')->where(
            array(
                'uri' => "/" . str_replace("_", "/", $this->selected_view_name) . "/",
                // $this->option['tracking_var'] => $_REQUEST[$this->option['tracking_var']],
                'variation' => $this->selected_view_name
            )
        )->all();


        if (!empty($result[0]['id'])) {
            $data = $result[0];
            $data['nb_visit'] = max(0, $result[0]['nb_visit']) + 1;
            $data['tx_conversion'] = round(($result[0]['nb_conversion'] / $data['nb_visit']) * 100, 1);
            if ($this->visit['device_type'] == "mobile") {
                $data['nb_visit_mobile'] = max(0, $result[0]['nb_visit_mobile']) + 1;
            } else if ($this->visit['device_type'] == "computer") {
                $data['nb_visit_desktop'] = max(0, $result[0]['nb_visit_desktop']) + 1;
            } else if ($this->visit['device_type'] == "tablet") {
                $data['nb_visit_tablet'] = max(0, $result[0]['nb_visit_tablet']) + 1;
            }
            $db->table('data_set')->update($result[0]['id'], $data);
        } else {
            $result = $db->table('data_set')->insert(
                $data
            );
        }
    }

    // /**
    //  * save conversion
    //  *
    //  * @return void
    //  */
    // private function save_conversion()
    // {
    //     if (!empty($_SESSION['URI']) && !empty($_SESSION['VARIATION']) && !empty($_SESSION['TEST'])) {
    //         $data = array(
    //             'uri' => strtolower($_SESSION['URI']),
    //             'variation' => $_SESSION['VARIATION']
    //         );
    //         foreach ($_SESSION['VAR'] as $k => $v) {
    //             $data[$k] = $v;
    //         }


    //         $db = new FlatDB(ABSPATH . LIB . "/George/database", $_SESSION['TEST']);
    //         // print_r($data);exit;
    //         @$result = @$db->table('data_set')->where(
    //             $data
    //         )->all();

    //         if (!empty($result[0]['id'])) {
    //             $data = $result[0];
    //             $data['nb_conversion'] = max(0, $result[0]['nb_conversion']) + 1;
    //             $data['tx_conversion'] = round(($data['nb_conversion'] /  $result[0]['nb_visit']) * 100, 1);

    //             if ($this->visit['device_type'] == "mobile") {
    //                 $data['nb_conversion_mobile'] = max(0, $result[0]['nb_conversion_mobile']) + 1;
    //             } else if ($this->visit['device_type'] == "tablet") {
    //                 $data['nb_conversion_tablet'] = max(0, $result[0]['nb_conversion_tablet']) + 1;
    //             } else {
    //                 $data['nb_conversion_desktop'] = max(0, $result[0]['nb_conversion_desktop']) + 1;
    //             }

    //             $db->table('data_set')->update($result[0]['id'], $data);
    //         }
    //     } else {
    //         return false;
    //     }
    // }


    /**
     * Save conversion with path
     *
     * @param string $path
     * @return void
     */
    public function save_conversion(string $path = ""): void
    {
        $data = array(
            'uri' => $path,
            'variation' => trim(str_replace("/", "_", $path), "_")
        );
        // foreach ($_SESSION['VAR'] as $k => $v) {
        //     $data[$k] = $v;
        // }


        $db = new FlatDB("database", $this->test);
        // print_r($data);exit;
        @$result = @$db->table('data_set')->where(
            $data
        )->all();

        if (!empty($result[0]['id'])) {
            $data = $result[0];
            $data['nb_conversion'] = max(0, $result[0]['nb_conversion']) + 1;
            $data['tx_conversion'] = round(($data['nb_conversion'] /  $result[0]['nb_visit']) * 100, 1);
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

    /**
     * Return data
     *
     * @return mixed
     */
    public function get_data_uri()
    {
        if (file_exists(ABSPATH . LIB . '/George/database/' . $this->test)) {
            $db = new FlatDB(ABSPATH . LIB . '/George/database', $this->test);

            @$result = @$db->table('data_set')->where(
                array(
                    'uri' => $this->visit['uri']
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
    }


    /**
     * Undocumented function
     *
     * @param array<string> $data
     * @return mixed
     */
    private function calculate_conversion(array $data)
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


    /**
     * Calculate
     *
     * @return bool
     */
    private function calculate(): bool
    {
        global $_REQUEST;
        $this->selected_view = @$this->variation[$this->option['default_view']];
        $this->selected_view_name = @$this->option['default_view'];

        // if (!empty($this->filter['utm_source']) and $this->filter['utm_source'] != $this->visit['utm_source']) {
        //     return false;
        // }

        // select view
        // get tracking data
        $data = $this->get_data_uri();

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
                $dump = $this->variation;
                unset($this->variation[$best_view_name]);
                if (empty($this->variation)) {
                    $this->variation = $dump;
                }
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
        // @$_SESSION['VAR'] = array($this->option['tracking_var'] => $_REQUEST[$this->option['tracking_var']]);
        $_SESSION['VARIATION'] = $this->selected_view_name;
        $_SESSION['TEST'] = $this->test;
        // $_REQUEST['utm_campaign'] = @trim("-", $this->test . "-" . $this->selected_view_name . "-" . $_REQUEST['utm_campaign']);
        return true;
    }


    /**
     * Return IP
     *
     * @return string
     */
    public function get_ip(): string
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

    /**
     * Add variation
     *
     * @param array<mixed, array<string, mixed>> $array
     * @return void
     */
    private function add_variation(array $array): void
    {
        foreach ($array as $k => $v) {
            $this->variation[$k] = $v;
        }
    }

    /**
     * render
     *
     * @param string $k
     * @return string
     */
    private function render(string $k): string
    {
        return $this->selected_view[strtolower($k)];
    }


    /**
     * Get list of all DB in directory database
     * @return array<array>
     */
    private function show_allData(): array
    {
        $dirs = scandir("database"); //On récupère le nom de toutes les DB disponibles
        unset($dirs[array_search(".", $dirs)]); //On Supprime les deux premiers éléments du tableau
        unset($dirs[array_search("..", $dirs)]); //On Supprime les deux premiers éléments du tableau
        unset($dirs[array_search("archived", $dirs)]); //On affiche pas les DB archivées

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
     * Get list of all DB in directory database archived
     * @return array<array>
     */
    private function show_archivedData(): array
    {
        $dirs = scandir("database/archived"); //On récupère le nom de toutes les DB disponibles
        unset($dirs[array_search(".", $dirs)]); //On Supprime les deux premiers éléments du tableau
        unset($dirs[array_search("..", $dirs)]); //On Supprime les deux premiers éléments du tableau

        $ListDB = [];
        foreach ($dirs as $db) {
            @$db = new FlatDB('database/archived', $db);
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
     * @return bool
     */
    public function deleteData(string $nameDB): bool
    {
        try {
            foreach (new DirectoryIterator($nameDB) as $item) {
                if ($item->isFile()) unlink($item->getRealPath());
                if (!$item->isDot() && $item->isDir()) $this->deleteData($item->getRealPath());
            }
            rmdir($nameDB);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create DB for ABTEST
     *
     * @param string $url_conversion
     * @param string $discovery_rate
     * @param array<string> $urls_variation
     * @return bool
     */
    public function registerInDB(string $url_conversion, string $discovery_rate, array $urls_variation): bool
    {
        if (!file_exists('database/' . $this->test)) {
            //Create DB for principal
            $db = new FlatDB('database', $this->test);
            $data = array(
                'uri' => $url_conversion,
                'variation' => $this->test,
                'listVariation' => $urls_variation,
                'discovery_rate' => $discovery_rate,
                'default_view' => $this->test,
                'nb_visit' => 0,
                'nb_visit_mobile' => 0,
                'nb_visit_desktop' => 0,
                'nb_visit_tablet' => 0,
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
                $dataVariation = array(
                    'uri' => $entry['uri'],
                    'variation' => $entry['name'],
                    'nb_visit' => 0,
                    'nb_visit_mobile' => 0,
                    'nb_visit_desktop' => 0,
                    'nb_visit_tablet' => 0,
                    'nb_conversion' => 0,
                    'tx_conversion' => 0,
                    'nb_conversion_mobile' => 0,
                    'nb_conversion_tablet' => 0,
                    'nb_conversion_desktop' => 0
                );

                $db->table('data_set')->insert(
                    $dataVariation
                );
            }
            return true;
        } else {
            return false;
        }
    }
    /**
     * Change State of ABTEST
     *
     * @return bool
     */
    public function changeStatus(): bool
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

        try {
            $db->table('data_set')->update($result[0]['id'], $data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * verify if string is contains
     *
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    private function str_contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }


    /**
     * Archive or Not ABTEST
     *
     * @return bool
     */
    public function setArchive(): bool
    {
        if (file_exists('database/' . $this->test)) {
            //Ne pas deArchived car existe déjà un fichier ici
            if (!file_exists('database/archived' . $this->test)) {
                rename('database/' . $this->test, 'database/archived/' . $this->test . "_" . date("Y-m-d_H-i-s"));
                return true;
            } else {
                return false;
            }
        } else if (file_exists('database/archived/' . $this->test)) {
            if (!file_exists('database/' . $this->test)) {
                rename('database/archived/' . $this->test, 'database/' . $this->test);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Get data for one ABTEST
     *
     * @return array
     */
    public function get_data_all()
    {
        if (file_exists(ABSPATH . LIB . '/George/database/' . $this->test)) {
            // $db = new FlatDB('database', $this->test);
            $db = new FlatDB(ABSPATH . LIB . '/George/database', $this->test);

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


    /**
     * Get data for one ABTEST
     *
     * @return array
     */
    public function get_data_by_abtest()
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

    /**
     *
     * @param string $nameDatabase
     * @return array|string|bool
     */
    public function get_data_variation(string $nameDatabase = "")
    {
        if (file_exists('database/' . $nameDatabase)) {
            $db = new FlatDB('database', $nameDatabase);
            @$result = @$db->table('data_set')->where(
                array(
                    'variation' => $nameDatabase
                )
            )->all();

            // exit;
            if (empty($result)) {
                return "false";
            } else {
                return $result;
            }
        }
    }

    /**
     *
     * @param string $nameDatabase
     * @return array|string|bool
     */
    public function get_data_debug(string $nameDatabase = "")
    {
        if (empty($nameDatabase)) {
            $nameDatabase = $this->test;
        }
        $db = new FlatDB(ABSPATH . LIB . '/George/database', $nameDatabase);
        @$result = @$db->table('data_set')->where()->all();

        // exit;
        if (empty($result)) {
            return "false";
        } else {
            return $result;
        }
    }

    /**
     * Display all DB
     *
     * @return string
     */
    public function draw_allData(string $statusSearch = ""): string
    {
        if ($statusSearch == "archived") {
            $allDb = $this->show_archivedData();
        } else {
            $allDb = $this->show_allData();
        }

        $play = '<div class="tab-pane fade show active " id="pills-play" role="tabpanel" aria-labelledby="pills-play-tab"><div class="listDB">';
        $pause = '<div class="tab-pane fade " id="pills-pause" role="tabpanel" aria-labelledby="pills-pause-tab"><div class="listDB">';
        $archived = '';


        foreach ($allDb as $oneDB) {
            $t = "";
            if ($statusSearch == "archived") {
                $t .= '<div class="card card-archived">';
            } else {
                $t .= '<div class="card">';
            }
            $t .=   '<div class="cadre-text p-3 row">
                        <div class="col-12 col-md-4 mt-2">
                            <p><u>Date</u> : <b>' . $oneDB[0]['date_time']->format('d/m/Y H:i') . '</b></p>';
            if ($oneDB[0]['status'] == 0 && $statusSearch != "archived") {
                $t .=           "<p><u>Status</u> : <b class='text-primary'>En cours</b></p>";
            } else if ($statusSearch == "archived") {
                $t .=           "<p><u>Status</u> : <b class='text-warning'>Archived</b></p>";
            } else {
                $t .=           "<p><u>Status</u> : <b class='text-warning'>En pause</b></p>";
            }
            $t .=           '<p><u>Discovery Rate</u> : <b>' . $oneDB[0]['discovery_rate'] * 100 . '%</b></p>';
            $t .=           "<div class='d-flex justify-content-evenly'>";
            if ($statusSearch != "archived") {
                $t .=               "<a class='btn btn-outline-danger' href='switchGeorge.php?archived=false&action=delete&db=" . $oneDB[0]['variation'] . "'>Delete</a>";
            }
            if ($oneDB[0]['status'] == 0 && $statusSearch != "archived") {
                $t .=               "<a class='btn btn-outline-warning ml-3' href='switchGeorge.php?action=changeState&db=" . $oneDB[0]['variation'] . "'>Pause</a>";
            } else if ($statusSearch != "archived") {
                $t .=               "<a class='btn btn-outline-primary ml-3' href='switchGeorge.php?action=changeState&db=" . $oneDB[0]['variation'] . "'>Reprendre</a>";
            }
            $t .=           '</div>';
            $t .=       '</div>';
            $t .=   '<div class="col-12 col-md-8">';
            foreach ($oneDB as $index => $entry) {
                $t .=   '<div class="col-12 mt-2">';
                $t .=       '<p><u>Variation</u> : ' . trim($entry['uri'], "/") . '</p>';
                $t .=       '<div class="row justify-content-center text-center mx-auto">';
                $t .=           '<div class="col-12 col-sm-6 col-md-4">
                                    <div class="roundedCardText mx-auto">
                                        <div>V</div>
                                        <div><b>(' . $entry['nb_visit'] . ')</b></div>
                                    </div>
                                </div>';
                $t .=           '<div class="col-12 col-sm-6 col-md-4">
                                    <div class="roundedCardText mx-auto">
                                        <div>C</div>
                                        <div><b>(' . $entry['nb_conversion'] . ')</b></div>
                                    </div>
                                </div>';

                $t .=           '<div class="col-12 col-sm-6 col-md-4">
                                    <div class="roundedCardText mx-auto">
                                        <div>TX</div>
                                        <div><b>(' . $entry['tx_conversion'] . '%)</b></div>
                                    </div>
                                </div>';
                $t .=           '</div>';
                $t .=       '</div>';
            }
            $t .= '</div>';
            if ($oneDB[0]['status'] == 0 && $statusSearch != "archived") {
                $t .= '</div><div class="bottomBar bg-primary text-white text-center"><a href="page_abtest.php?dbName=' . $oneDB[0]['variation'] . '"><h5>' . trim($oneDB[0]['uri'], '/')  . '</h5></a></div>';
            } else if ($statusSearch == "archived") {
                $t .= '</div><div class="bottomBar bg-secondary text-white text-center"><a href="#"><h5>' . trim($oneDB[0]['uri'], '/')  . '</h5></a></div>';
            } else {
                $t .= '</div><div class="bottomBar bg-secondary text-white text-center"><a href="page_abtest.php?dbName=' . $oneDB[0]['variation'] . '"><h5>' . trim($oneDB[0]['uri'], '/')  . '</h5></a></div>';
            }
            $t .= "</div>";

            if ($oneDB[0]['status'] == 0 && !$statusSearch == "archived") {
                $play .= $t;
            }

            if ($oneDB[0]['status'] == 1 && !$statusSearch == "archived") {
                $pause .= $t;
            }

            if ($statusSearch == "archived") {
                $archived .= $t;
            }
        }

        $play .= '</div></div>';
        $pause .= '</div></div>';
        $archived .= '';

        $display = $play . $pause . $archived;

        return $display;
    }

    /**
     * Sort array multidimentional
     * @param array<string> $array
     * @param array<string, int> $cols
     * @return array<string>
     */
    private function array_msort(array $array, array $cols): array
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
    public function draw_abtest(): string
    {
        $abtest = $this->get_data_by_abtest();

        if (isset($_GET['debug'])) {
            var_dump($abtest);
        }

        $state = $abtest[0]['status'] == 0 ? "En cours" : "En pause";
        $draw = '
            <div class="headerCard">';
        if ($this->str_contains($abtest[0]['variation'], "archived_")) {
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
                            <a class="dropdown-item text-info" href="switchGeorge.php?action=changeState&db=' . $abtest[0]['variation'] . '">Pause/Play</a>';

        if ($state != "Archivé") {
            $draw .= '<a class="dropdown-item text-warning" href="switchGeorge.php?action=setArchive&db=' . $abtest[0]['variation'] . '">Archiver</a>
                    <a class="dropdown-item text-danger" href="switchGeorge.php?archived=false&action=delete&db=' . $abtest[0]['variation'] . '">/!\ Supprimer</a>';
        } else {
            $draw .= '<a class="dropdown-item text-danger" href="switchGeorge.php?archived=true&action=delete&db=' . $abtest[0]['variation'] . '">/!\ Supprimer</a>';
        }

        $draw .= '</div>
                </div>
            </div>
        </div>';

        $draw .= '<div class="d-flex align-items-center justify-content-center flex-wrap">';

        $abtest = $this->array_msort($abtest, array('tx_conversion' => SORT_DESC, 'nb_visit' => SORT_DESC)); //Permet de trier un tableau multidimensionnel par ordre décroissant

        $i = 0;

        foreach ($abtest as $key => $value) {
            if ($i == 0) {
                $draw .= '<div class="col-12 col-sm-6">
                            <div class="card text-center text-success"><b>WINNER : ' . $value['uri'] . '</b></div>
                            <div class="card"> 
                                <div class="container mx-auto">
                                    <ul class="nav nav-pills justify-content-left mb-3 w-100" id="pills-tab" role="tablist">
                                        <li class="nav-item mx-auto" role="presentation">
                                            <a class="ml-3 nav-link btn btn-outline-primary active" id="pills-tx-tab" data-toggle="pill" href="#pills-tx" role="tab" aria-controls="pills-tx" aria-selected="true">Conversion</a>
                                        </li>
                                        <li class="nav-item mx-auto" role="presentation">
                                            <a class="ml-3 nav-link btn btn-outline-primary" id="pills-visit-tab" data-toggle="pill" href="#pills-visit" role="tab" aria-controls="pills-visit" aria-selected="false">Visite</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active " id="pills-tx" role="tabpanel" aria-labelledby="pills-tx-tab">
                                        <canvas id="donut_tx_conversion"></canvas>
                                    </div>
                                    <div class="tab-pane fade" id="pills-visit" role="tabpanel" aria-labelledby="pills-visit-tab">
                                        <canvas id="donut_visit"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }
            $draw .= '<div class="card col-12 col-sm-6">';
            $draw .= '<h2 class="text-center">' . $value['variation'] . '</h2>';
            $draw .= '<p>Nombre visiteur(s) : <b>M(' . $value['nb_visit_mobile'] . ')</b> | <b>D(' . $value['nb_visit_desktop'] . ')</b> | <b>T(' . $value['nb_visit_tablet'] . ')</b></p>';
            $draw .= '<div class="row justify-content-center align-items-center">';
            $draw .= '<div class="col-12 col-sm-6">
                            <h6 class="mt-5 text-center">Nombre de visiteur</h6>
                            <div class="roundedCardText mx-auto">
                                <div><b>' . $value['nb_visit'] . '</b></div>
                            </div>
                        </div>';
            $draw .= '<div class="col-12 col-sm-6">
                    <h6 class="mt-5 text-center">Convertion mobile</h6>
                    <div class="roundedCardText mx-auto">
                        <div><b>' .  round(($value['nb_conversion_mobile'] / $value['nb_visit_mobile']) * 100, 1) . '%</b></div>
                    </div>
                </div>';

            $draw .= '<div class="col-12 col-sm-6">
                        <h6 class="mt-5 text-center">Conversion tablette</h6>
                        <div class="roundedCardText mx-auto">
                            <div><b>' . round(($value['nb_conversion_tablet'] / $value['nb_visit_tablet']) * 100, 1) . '%</b></div>
                        </div>
                    </div>';

            $draw .= '<div class="col-12 col-sm-6">
                    <h6 class="mt-5 text-center">Conversion PC</h6>
                    <div class="roundedCardText mx-auto">
                        <div><b>' . round(($value['nb_conversion_desktop'] / $value['nb_visit_desktop']) * 100, 1) . '%</b></div>
                    </div>
                </div>';

            $draw .= '<div class="col-12 col-sm-6">
                <h6 class="mt-5 text-center">Conversion total</h6>
                <div class="roundedCardText mx-auto">
                    <div><b>' . $value['nb_conversion'] . '</b></div>
                </div>
            </div>';

            $draw .= '<div class="col-12 col-sm-6">
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
