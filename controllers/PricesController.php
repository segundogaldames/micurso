<?php

namespace controllers;

use application\Controller;
use application\Filter;
use application\Flash;
use application\Helper;
use application\Session;
use application\Validate;
use models\Price;

class PricesController extends Controller
{
    private $_module;

    public function __construct()
    {
        $this->validateSession();
        parent::__construct(); 
        $this->_module = $this->getModule('Price');
    }

    public function index()
    {
        //Helper::debuger($this->_module);
        $this->validatePermission($this->_module, 'Listar');
        $this->_view->load('prices/index', [
            'title' => 'Precios',
            'prices'  => Price::select('id','name','value')->get(),
            'subject' => 'Lista de Precios',
            'action' =>  'index',
            'route_create' => "prices/create",
            'button_create' => 'Nuevo Precio'
        ]);
    }

    public function create()
    {
        $this->validatePermission($this->_module, 'Crear');
        $this->_view->load('prices/create', [
            'title' => 'Precios',
            'subject' => 'Nuevo Precio',
            'price' => Session::get('form_data') ?? [],
            'send'   => $this->encrypt($this->getForm()),
            'process' => "prices/store",
            'action' => 'create',
        ]);
    }

    public function store()
    {
        $this->validatePermission($this->_module, 'Crear');
        $data = [
            'name' => Filter::getPost('name'),
            'value' => Filter::getPost('value')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string|unique:prices,name',
            'value' => 'required|numeric'
        ];

        $this->validateForm('prices/create', $data, $rules);

        $price = new Price();
        $price->name = Helper::getTitle($data['name']);
        $price->value = (int) $data['value'];
        $price->save();

        Flash::success('Precio creado correctamente.');
        $this->redirect('prices');
    }

    public function show($id = null)
    {
        $this->validatePermission($this->_module, 'Leer');
        $price = Validate::validateModel(Price::class, $id, 'prices');

        $this->_view->load('prices/show', [
            'title' => 'Precios',
            'subject' => 'Detalle Precio',
            'price'  => $price
        ]);
    }

    public function edit($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $price = Validate::validateModel(Price::class, $id, 'prices');

        $this->_view->load('prices/edit', [
            'title' => 'Precios',
            'subject' => 'Editar Precio',
            'price'   => $price,
            'send'   => $this->encrypt($this->getForm()),
            'process' => "prices/update/{$id}",
            'action' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        $this->validatePermission($this->_module, 'Editar');
        $this->validatePUT();
        $price = Validate::validateModel(Price::class, $id, 'prices');

        $data = [
            'name' => Filter::getPost('name'),
            'value' => Filter::getPost('value')
        ];

        $rules = [
            'name' => 'required|min:3|max:100|string',
            'value' => 'required|numeric'
        ];

        $this->validateForm("prices/edit/$id", $data, $rules);

        $price = $price;
        $price->name = Helper::getTitle($data['name']);;
        $price->value = (int) $data['value'];
        $price->save();

        Flash::success('Precio actualizado correctamente.');
        $this->redirect('prices/show/' . $id);
    }
}