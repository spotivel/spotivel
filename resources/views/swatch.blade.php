<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nord Theme Color Swatch</title>
    @vite('resources/css/themes/admin/theme.css')
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-nord-800 text-nord-100 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold text-nord-50 mb-8">Nord Theme Color Swatch</h1>
        
        <div class="mb-4 flex gap-4">
            <button onclick="document.documentElement.classList.toggle('dark')" 
                    class="bg-frost-700 hover:bg-frost-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                Toggle Light/Dark Mode
            </button>
        </div>

        <!-- Nord Base Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-nord-100 mb-4">Nord (Polar Night / Snow Storm)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                <div class="text-center">
                    <div class="bg-nord-50 h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-50</p>
                    <p class="text-xs text-nord-400">#ECEFF4</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-100 h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-100</p>
                    <p class="text-xs text-nord-400">#E5E9F0</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-200 h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-200</p>
                    <p class="text-xs text-nord-400">#D8DEE9</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-300 h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-300</p>
                    <p class="text-xs text-nord-400">#C2CAD0</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-400 h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-400</p>
                    <p class="text-xs text-nord-400">#A9B2BA</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-500 h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-500</p>
                    <p class="text-xs text-nord-400">#4C566A</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-600 h-24 rounded-lg border border-nord-700 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-600</p>
                    <p class="text-xs text-nord-400">#434C5E</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-700 h-24 rounded-lg border border-nord-800 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-700</p>
                    <p class="text-xs text-nord-400">#3B4252</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-800 h-24 rounded-lg border border-nord-900 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-800</p>
                    <p class="text-xs text-nord-400">#2E3440</p>
                </div>
                <div class="text-center">
                    <div class="bg-nord-900 h-24 rounded-lg border border-nord-950 mb-2"></div>
                    <p class="text-xs text-nord-300">nord-900</p>
                    <p class="text-xs text-nord-400">#242933</p>
                </div>
            </div>
        </section>

        <!-- Frost Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-nord-100 mb-4">Frost (Blue/Cyan)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                <div class="text-center">
                    <div class="bg-frost-{{ $shade }} h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">frost-{{ $shade }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- IVPL Colors (User's Special Blue) -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-ivpl-400 mb-4">IVPL (Special Blue #429AE1)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                <div class="text-center">
                    <div class="bg-ivpl-{{ $shade }} h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">ivpl-{{ $shade }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Red Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-red-400 mb-4">Red (Aurora)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                <div class="text-center">
                    <div class="bg-red-{{ $shade }} h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">red-{{ $shade }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Orange Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-orange-400 mb-4">Orange (Aurora)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                <div class="text-center">
                    <div class="bg-orange-{{ $shade }} h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">orange-{{ $shade }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Yellow Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-yellow-400 mb-4">Yellow (Aurora)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                <div class="text-center">
                    <div class="bg-yellow-{{ $shade }} h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">yellow-{{ $shade }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Green Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-green-400 mb-4">Green (Aurora)</h2>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-4">
                @foreach([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade)
                <div class="text-center">
                    <div class="bg-green-{{ $shade }} h-24 rounded-lg border border-nord-600 mb-2"></div>
                    <p class="text-xs text-nord-300">green-{{ $shade }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- UI Components with Colors -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-nord-100 mb-4">UI Components</h2>
            <div class="bg-nord-700 dark:bg-nord-700 p-6 rounded-lg border border-nord-600">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Buttons -->
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-3">Buttons</h3>
                        <div class="space-y-2">
                            <button class="bg-frost-700 hover:bg-frost-600 text-white px-4 py-2 rounded w-full transition-colors">Primary</button>
                            <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded w-full transition-colors">Success</button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded w-full transition-colors">Danger</button>
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-nord-800 px-4 py-2 rounded w-full transition-colors">Warning</button>
                            <button class="bg-ivpl-500 hover:bg-ivpl-600 text-white px-4 py-2 rounded w-full transition-colors">Info (IVPL)</button>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-3">Badges</h3>
                        <div class="space-y-2">
                            <div><span class="bg-frost-700 text-white px-3 py-1 rounded-full text-sm">Primary</span></div>
                            <div><span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Success</span></div>
                            <div><span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm">Danger</span></div>
                            <div><span class="bg-yellow-500 text-nord-800 px-3 py-1 rounded-full text-sm">Warning</span></div>
                            <div><span class="bg-ivpl-500 text-white px-3 py-1 rounded-full text-sm">Info</span></div>
                        </div>
                    </div>

                    <!-- Form Elements -->
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-3">Form Elements</h3>
                        <div class="space-y-2">
                            <input type="text" placeholder="Text input" 
                                   class="w-full bg-nord-600 border border-nord-500 text-nord-100 px-3 py-2 rounded focus:border-frost-700 focus:ring-1 focus:ring-frost-700">
                            <select class="w-full bg-nord-600 border border-nord-500 text-nord-100 px-3 py-2 rounded">
                                <option>Select option</option>
                                <option>Option 1</option>
                            </select>
                            <textarea placeholder="Textarea" rows="3" 
                                      class="w-full bg-nord-600 border border-nord-500 text-nord-100 px-3 py-2 rounded"></textarea>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-3">Alerts</h3>
                        <div class="space-y-2">
                            <div class="bg-green-900 border border-green-700 text-green-100 px-4 py-2 rounded">Success alert</div>
                            <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-2 rounded">Danger alert</div>
                            <div class="bg-yellow-900 border border-yellow-700 text-yellow-100 px-4 py-2 rounded">Warning alert</div>
                            <div class="bg-ivpl-900 border border-ivpl-700 text-ivpl-100 px-4 py-2 rounded">Info alert</div>
                        </div>
                    </div>

                    <!-- Cards -->
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-3">Cards</h3>
                        <div class="bg-nord-700 border border-nord-600 rounded-lg p-4">
                            <h4 class="text-nord-100 font-medium mb-2">Card Title</h4>
                            <p class="text-nord-300 text-sm">This is a card with Nord colors applied.</p>
                        </div>
                    </div>

                    <!-- Links -->
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-3">Links</h3>
                        <div class="space-y-2">
                            <div><a href="#" class="text-frost-600 hover:text-frost-500">Primary Link</a></div>
                            <div><a href="#" class="text-ivpl-500 hover:text-ivpl-400">IVPL Link</a></div>
                            <div><a href="#" class="text-green-500 hover:text-green-400">Success Link</a></div>
                            <div><a href="#" class="text-red-500 hover:text-red-400">Danger Link</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Color Usage Guide -->
        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-nord-100 mb-4">Color Usage Guide</h2>
            <div class="bg-nord-700 p-6 rounded-lg border border-nord-600">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-2">Backgrounds</h3>
                        <ul class="text-nord-300 space-y-1 text-sm">
                            <li><code class="bg-nord-600 px-2 py-1 rounded">nord-800</code> - Main background (darkest)</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">nord-700</code> - Cards, panels</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">nord-600</code> - Inputs, borders</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-2">Text</h3>
                        <ul class="text-nord-300 space-y-1 text-sm">
                            <li><code class="bg-nord-600 px-2 py-1 rounded">nord-50</code> - Headings, important text</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">nord-100/200</code> - Body text</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">nord-300</code> - Muted text, placeholders</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-nord-200 mb-2">Accents</h3>
                        <ul class="text-nord-300 space-y-1 text-sm">
                            <li><code class="bg-nord-600 px-2 py-1 rounded">frost-700</code> - Primary actions</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">ivpl-500</code> - Info, special (#429AE1)</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">green-500</code> - Success states</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">red-500</code> - Danger, errors</li>
                            <li><code class="bg-nord-600 px-2 py-1 rounded">yellow-500</code> - Warnings</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>