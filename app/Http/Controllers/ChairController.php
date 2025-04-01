<?php

namespace App\Http\Controllers;

use App\Chair;
use Illuminate\Http\Request;

class ChairController extends Controller
{
    public function index()
    {
        $chairs = Chair::all(); // Recupera todas as cadeiras
        return view('chairs.index', compact('chairs'));
    }

    public function create()
    {
        return view('chairs.create'); // Retorna a view para criar uma cadeira
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'localizacao' => 'nullable|string|max:255',
        ]);

        Chair::create($request->all()); // Salva os dados da cadeira

        return redirect()->route('chairs.index')->with('success', 'Cadeira criada com sucesso!');
    }

    public function edit(Chair $chair)
    {
        return view('chairs.edit', compact('chair')); // Retorna a view para editar uma cadeira
    }

    public function update(Request $request, Chair $chair)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'localizacao' => 'nullable|string|max:255',
        ]);

        $chair->update($request->all()); // Atualiza os dados da cadeira

        return redirect()->route('chairs.index')->with('success', 'Cadeira atualizada com sucesso!');
    }

    public function destroy(Chair $chair)
    {
        $chair->delete(); // Exclui a cadeira

        return redirect()->route('chairs.index')->with('success', 'Cadeira excluída com sucesso!');
    }
}
