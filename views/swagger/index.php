<?php
/** @var yii\web\View $this */
/** @var string $schemaUrl */

use yii\helpers\Json;

$this->title = 'API документация';
?>
<style>
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
    }

    #swagger-ui {
        height: 100vh;
    }
</style>

<div id="swagger-ui"></div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.min.js"></script>
<script>
    window.addEventListener('load', function () {
        SwaggerUIBundle({
            url: <?= Json::encode($schemaUrl) ?>,
            dom_id: '#swagger-ui',
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            layout: 'BaseLayout'
        });
    });
</script>