<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Company API</h1>
            <p class="text-gray-600">Тестове завдання на вакансію PHP Backend Developer (Laravel)</p>
            <br>
            <p class="text-gray-600">Виконав: <i>Ющик Андрій Вікторович</i></p>
        </div>

        <div class="space-y-6">
            <!-- API Documentation -->
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Swagger UI Документація</h2>
                <p class="text-gray-600 mb-4">Swagger UI для перегляду документації з можливістю тестування API endpoints</p>
                <a href="/api/documentation" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Відкрити Swagger UI
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>

            <!-- Features -->
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Функціонал</h2>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start">
                        <span class="material-icons text-green-500 mr-2 mt-0.5">blur_on</span>
                        <span>Створення/Оновлення компанії з підтримкою версійності змін</span>
                    </li>
                    <li class="flex items-start">
                        <span class="material-icons text-green-500 mr-2 mt-0.5">blur_on</span>
                        <span>Перегляд всії версій компанії за її ЄДРПОУ</span>
                    </li>
                    <li class="flex items-start">
                        <span class="material-icons text-green-500 mr-2 mt-0.5">blur_on</span>
                        <span>Перегляд список усіх компаній збережених в системі</span>
                    </li>
                    <li class="flex items-start">
                        <span class="material-icons text-green-500 mr-2 mt-0.5">blur_on</span>
                        <span>Створено <i>companies</i> i <i>company_versions</i> міграції для створення таблиць БД</span>
                    </li>
                    <li class="flex items-start">
                        <span class="material-icons text-green-500 mr-2 mt-0.5">blur_on</span>
                        <span>Створено <i>CompanySeeder</i> для наповнення списку компаній</span>
                    </li>
                </ul>
            </div>

        <div class="mt-8 text-center text-sm text-gray-500">
            <p>Laravel {{ app()->version() }} • PHP {{ phpversion() }}</p>
        </div>
    </div>
</div>
</body>
</html>
