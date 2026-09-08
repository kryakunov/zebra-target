@php
    $seo = isset($seo) ? $seo : [];
    $h1 = $seo['h1'] ?? ($page->type_name ?? 'Инструмент Зебра Таргет');
    $intro = $seo['intro'] ?? 'Инструмент помогает искать и собирать целевую аудиторию ВКонтакте. Описание доступно без входа, запуск задачи — после авторизации.';
    $forWhom = $seo['for_whom'] ?? 'Для таргетологов, SMM и предпринимателей, которым нужна аудитория из ВКонтакте для рекламы и анализа.';
    $how = $seo['how'] ?? 'Авторизуйтесь через ВК, укажите исходные данные, задайте параметры и создайте задачу. Результат появится в менеджере задач.';
    $benefits = $seo['benefits'] ?? [
        'Понятный сценарий работы с аудиторией ВК',
        'Результат сохраняется и его можно использовать в других инструментах',
        'Часть функций доступна на бесплатном доступе',
    ];
    $faq = $seo['faq'] ?? [
        ['q' => 'Можно ли пользоваться без регистрации?', 'a' => 'Страницу инструмента можно читать без входа. Запуск парсинга и сохранение результатов требуют авторизации через ВКонтакте.'],
        ['q' => 'Где посмотреть результат?', 'a' => 'После запуска задача появляется в менеджере задач. Оттуда можно скачать данные или отправить их в следующий инструмент.'],
    ];
@endphp

<h1 class="title">{{ $h1 }}</h1>
<p>{{ $intro }}</p>

<h2>Для кого этот инструмент</h2>
<p>{{ $forWhom }}</p>

<h2>Как это работает</h2>
<p>{{ $how }}</p>

<h2>Преимущества</h2>
<ul class="text-start">
    @foreach ($benefits as $benefit)
        <li>{{ $benefit }}</li>
    @endforeach
</ul>

@include('partials.guest-cta')

@if (count($faq))
    <h2>Частые вопросы</h2>
    @foreach ($faq as $item)
        <h3>{{ $item['q'] }}</h3>
        <p>{{ $item['a'] }}</p>
    @endforeach
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($item) {
        return [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['a'],
            ],
        ];
    }, $faq),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
