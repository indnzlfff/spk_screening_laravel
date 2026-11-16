<?php

namespace App\Http\Controllers;

use App\Services\NaiveBayesScreeningService as Model;
use Illuminate\View\View;
use RuntimeException;

class EvaluationController extends Controller
{
    public function __invoke(Model $model): View
    {
        $rows = $model->trainingData();
        $result = null;
        $message = null;

        try {
            $result = Model::evaluate($rows);
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
        }

        return view('admin.evaluation', compact('result', 'message'));
    }
}
