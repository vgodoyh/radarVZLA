<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Datos de X (ya cacheados por el comando UpdateXTimelines)
        $xFeeds = Cache::get('x_timelines_dashboard', []);

        // KPIs, alertas, etc. — según cómo los estés generando
        $kpis = $this->getKpis();
        $ongs = $this->getOngsData();

        return view('x-feeds', compact('xFeeds', 'kpis', 'ongs'));
    }

    private function getKpis()
    {
        // lógica para calcular/obtener KPIs
        return [];
    }

    private function getOngsData()
    {
        // datos por ONG (Acceso a la Justicia, Fake News, JEP, OBU)
        return [];
    }
}