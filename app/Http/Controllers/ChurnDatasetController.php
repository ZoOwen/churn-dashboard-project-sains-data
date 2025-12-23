<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChurnDataset;

class ChurnDatasetController extends Controller
{
     public function index()
    {
         $datasets = ChurnDataset::all();

        return view('v1.index', compact('datasets'));
    }
}
