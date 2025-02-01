<!DOCTYPE html>
<html>
<head>
    <title>Artisan GUI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .command-card {
            height: 100%;
            transition: transform 0.2s;
        }
        .command-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .argument-form {
            /* display: none; */
            margin-bottom: 1rem;
        }
        .argument-form.active {
            display: block;
        }
        .group-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .group-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: #0d6efd;
        }
        .group-header {
            cursor: pointer;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .group-header:hover {
            background: #e9ecef;
        }
        .group-header .chevron {
            transition: transform 0.3s;
        }
        .group-header.collapsed .chevron {
            transform: rotate(-90deg);
        }
        .command-group {
            margin-bottom: 2rem;
        }
        .commands-container {
            transition: all 0.3s;
        }
        .output-error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .output-success {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .modal-xl {
            max-width: 90%;
        }
        .output-container {
            max-height: 70vh;
            overflow-y: auto;
        }
        .modal-xl{
            max-width: 50%;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h2 class="mb-4 text-center">Artisan Commands</h2>

        @foreach($commands as $group => $groupCommands)
        <div class="command-group">
            <div class="group-header collapsed"
                 data-bs-toggle="collapse"
                 data-bs-target="#group-{{ $group }}"
                 aria-expanded="false">
                <h3 class="mb-0 text-capitalize">
                    {{ $group }}
                    <span class="badge bg-primary ms-2">{{ count($groupCommands) }}</span>
                </h3>
                <svg class="chevron" width="24" height="24" viewBox="0 0 16 16">
                    <path fill="currentColor" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </div>
            <div class="collapse commands-container" id="group-{{ $group }}">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($groupCommands as $name => $details)
                    <div class="col">
                        <div class="card command-card">
                            <div class="card-body">
                                <h5 class="card-title text-primary">{{ str_replace($group . ':', '', $name) }}</h5>
                                <p class="card-text">{{ $details['description'] }}</p>

                                <form class="argument-form" id="form-{{ $name }}">
                                    @if(count($details['arguments']) > 0)
                                    <div class="mb-3">
                                        <h6 class="text-muted">Arguments</h6>
                                        @foreach($details['arguments'] as $argName => $arg)
                                        <div class="mb-2">
                                            <label class="form-label">
                                                {{ $argName }}
                                                @if($arg['required'])<span class="text-danger">*</span>@endif
                                            </label>
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="arguments[{{ $argName }}]"
                                                   @if($arg['default']) placeholder="Default: {{ $arg['default'] }}"@endif
                                                   @if($arg['required']) required @endif>
                                            <small class="text-muted">{{ $arg['description'] }}</small>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    @if(count($details['options']) > 0)
                                    <div class="mb-3">
                                        <h6 class="text-muted">Options</h6>
                                        @foreach($details['options'] as $optName => $opt)
                                        @if($opt['acceptValue'])
                                        <div class="mb-2">
                                            <label class="form-label">--{{ $optName }}</label>
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   name="options[{{ $optName }}]"
                                                   @if($opt['default']) placeholder="Default: {{ $opt['default'] }}"@endif>
                                            <small class="text-muted">{{ $opt['description'] }}</small>
                                        </div>
                                        @else
                                        <div class="mb-2 form-check">
                                            <input type="checkbox"
                                                   class="form-check-input"
                                                   name="options[{{ $optName }}]"
                                                   id="opt-{{ $name }}-{{ $optName }}">
                                            <label class="form-check-label" for="opt-{{ $name }}-{{ $optName }}">
                                                --{{ $optName }}
                                            </label>
                                            <small class="d-block text-muted">{{ $opt['description'] }}</small>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                    @endif
                                </form>

                                <button class="btn btn-primary w-100" onclick="executeCommand('{{ $name }}')">
                                    Execute Command
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="modal fade" id="outputModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Command Output
                        <span id="outputStatus" class="badge"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body output-container">
                    <pre id="commandOutput" class="p-3 rounded"></pre>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function executeCommand(command) {
            const form = document.getElementById(`form-${command}`);
            const formData = new FormData(form);
            const arguments = {};
            const options = {};

            formData.forEach((value, key) => {
                if (key.startsWith('arguments[')) {
                    const argName = key.match(/arguments\[(.*?)\]/)[1];
                    arguments[argName] = value;
                } else if (key.startsWith('options[')) {
                    const optName = key.match(/options\[(.*?)\]/)[1];
                    options[optName] = value === 'on' ? true : value;
                }
            });

            fetch('/artisan/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    command: command,
                    arguments: arguments,
                    options: options
                })
            })
            .then(response => response.json())
            .then(data => {
                const output = document.getElementById('commandOutput');
                const status = document.getElementById('outputStatus');

                if (data.success) {
                    output.className = 'p-3 rounded output-success';
                    status.className = 'badge bg-success';
                    status.textContent = 'Success';
                } else {
                    output.className = 'p-3 rounded output-error';
                    status.className = 'badge bg-danger';
                    status.textContent = 'Error';
                }

                output.textContent = data.success ? data.output : `Error: ${data.error}`;
                new bootstrap.Modal(document.getElementById('outputModal')).show();
            })
            .catch(error => {
                const output = document.getElementById('commandOutput');
                const status = document.getElementById('outputStatus');

                output.className = 'p-3 rounded output-error';
                status.className = 'badge bg-danger';
                status.textContent = 'Error';
                output.textContent = `System Error: ${error.message}`;
                new bootstrap.Modal(document.getElementById('outputModal')).show();
            });
        }

        document.querySelectorAll('.group-header').forEach(header => {
            header.addEventListener('click', () => {
                header.classList.toggle('collapsed');
            });
        });
    </script>
</body>
</html>
