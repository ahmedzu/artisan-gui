<?php

namespace Ahmedzu\ArtisanGui\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controller;


class ArtisanController extends Controller
{
    public function index()
    {
        $commands = $this->getGroupedCommands();
        return view('artisan-gui::index', compact('commands'));
    }

    public function execute(Request $request)
    {
        $command = $request->input('command');
        $arguments = $request->input('arguments', []);
        $options = $request->input('options', []);

        // Filter out empty arguments
        $arguments = array_filter($arguments, fn($value) => $value !== '');

        // Process options: convert checkbox values to proper boolean flags
        $processedOptions = [];
        foreach ($options as $key => $value) {
            if ($value === true || $value === 'true' || $value === 'on') {
                // For checkbox options, just include the key
                $processedOptions['--' . $key] = true;
            } else if ($value !== '' && $value !== null) {
                // For options with values
                $processedOptions['--' . $key] = $value;
            }
        }

        // Combine arguments and processed options
        $parameters = array_merge($arguments, $processedOptions);

        try {
            Artisan::call($command, $parameters);
            $output = Artisan::output();
            return response()->json(['success' => true, 'output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function getGroupedCommands()
    {
        $commands = $this->getCommands();
        $grouped = [];

        foreach ($commands as $name => $details) {
            $group = explode(':', $name)[0];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][$name] = $details;
        }

        ksort($grouped);
        return $grouped;
    }

    private function getCommands()
    {
        $commands = [];
        foreach (Artisan::all() as $name => $command) {
            $definition = $command->getDefinition();
            $commands[$name] = [
                'description' => $command->getDescription(),
                'arguments' => collect($definition->getArguments())->map(function($argument) {
                    return [
                        'description' => $argument->getDescription(),
                        'default' => $argument->getDefault(),
                        'required' => $argument->isRequired()
                    ];
                })->toArray(),
                'options' => collect($definition->getOptions())->map(function($option) {
                    return [
                        'description' => $option->getDescription(),
                        'default' => $option->getDefault(),
                        'shortcut' => $option->getShortcut(),
                        'acceptValue' => $option->acceptValue()
                    ];
                })->toArray()
            ];
        }
        return $commands;
    }
}
