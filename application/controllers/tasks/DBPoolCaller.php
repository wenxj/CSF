<?php

class DBPoolCaller extends CoreController
{
    private $serv = null;
    public function __construct(Array $params)
    {
        parent::__construct($params);
        $this->serv = $params["serv"];
    }


    public function process($data)
    {
        static $maps = [];

        $model = $data["model"];
        $method = $data["method"];
        $params = $data["params"];
        if (!$model || !$method || !$params) {
            $this->serv->finish(false);
        } else {
            $obj = $maps[$model];
            if (!$obj) {
                $this->load->model($model);
                $obj = $maps[$model] = $this->$model;
            }

            try {
                $results = $obj->$method($params);
            } catch (Exception $e) {
                $this->load = &loadClass("CoreLoader", null, null, false);
                $this->$model = null;
                $this->load->model($model);
                $this->$model->loadDb();
                $obj = $maps[$model] = $this->$model;
                $results = $obj->$method($params);
            }
        }

        $this->serv->finish(json_encode($results));
    }
}
