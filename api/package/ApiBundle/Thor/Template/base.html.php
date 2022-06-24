<!doctype html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>API Documentation</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" referrerpolicy="no-referrer" />

    <!--Custom Script-->
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        })
    </script>

    <!-- Custom Style -->
    <style>
        * {
            font-size: .95rem;
        }
        .btn:focus,
        .accordion-button:focus {
            outline: none;
            box-shadow: none;
        }
        .pointer{
            cursor: pointer;
        }
        .px-3{
            padding-left: .75rem !important;
            padding-right: .75rem !important;
        }
        .accordion-button {
            padding: .4rem .6rem;
        }
        .header{
            background: #5b5e67;
        }

        .accordion-button::after{
            margin-left: 0;
        }

        .accordion-button:not(.collapsed) {
            box-shadow: none;
        }

        .accordion-body{
            border-top: 1px solid rgba(0,0,0,.125);
            padding: .75rem;
        }

        .accordion-body > *{
            margin-bottom: .75rem;
        }

        .accordion-body > *:last-child{
            margin-bottom: 0;
        }

        .alert{
            padding: .5rem 1rem;
        }

        .route {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .route .method {
            min-width: 65px;
        }
        .route .method.clear {
            min-width: inherit;
        }
        .route .method a,
        .route .method span {
            border-radius: .2rem;
            padding: .2rem .4rem;
            font-weight: 500;
            color: #FFF;
        }
        .route .method.get span {
            background: #49cc90;
        }
        .route .method.post span {
            background: #61affe;
        }
        .route .method.put span {
            background: #fca130;
        }
        .route .method.patch span {
            background: #a730fc;
        }
        .route .method.delete span {
            background: #f93e3e;
        }
        .route .uri {
            font-weight: 500;
            padding: .2rem .4rem;
            border: 1px solid rgba(0,0,0,.1);
            border-radius: .25rem;
            color: rgba(0,0,0,.75);

        }
        .route .desc{
            color: rgba(0,0,0,.75);
        }
        .title{
            font-weight: 400;
            color: rgba(0,0,0,.75);
        }
    </style>
</head>
<body class="bg-light">
<main class="container">
    <!--
        Header
    -->
    <div class="d-flex align-items-center p-3 my-3 text-white rounded shadow-sm header">
        <i class="fa-solid fa-book fa-2xl"></i>
        <div class="ms-3">
            <h1 class="h5 mb-1 text-white lh-1">API Documentation</h1>
            <small>Version: <?php echo $version ? date('d.m.Y H:i', $version) : 'Latest'; ?></small>
        </div>
        <div class="ms-auto">
            <div class="btn-group btn-group-sm mb-2">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-success dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-download me-2"></i> Download
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $this->router->generate('thor.download', ['version' => $version ?? 'latest']); ?>"><i class="fa-brands fa-js-square me-2"></i>TypeScript</a></li>
                    </ul>
                </div>

                <div class="btn-group btn-group-sm">
                    <button class="btn btn-warning dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-code-branch me-2"></i> Version
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $this->router->generate('thor.view'); ?>">Latest</a></li>
                        <?php foreach ($docs as $doc) { ?>
                            <li><a class="dropdown-item" href="<?php echo $this->router->generate('thor.view', ['version' => $doc->getBasename('.json')]); ?>"><?php echo date('d.m.Y H:i', $doc->getBasename('.json')); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="text-end"><?php echo $this->bag->get('thor.base_url'); ?></div>
        </div>
    </div>

    <div class="row">
        <!--
            Sidebar
        -->
        <div class="col-3">
            <div id="lists" class="list-group sticky-top bg-body rounded" style="top: 1rem">
                <?php foreach ($data as $groupName => $routes) { ?>
                    <a class="list-group-item list-group-item-action" href="#<?php echo $groupName ?? '/'; ?>"><?php echo $groupName ?? '/'; ?></a>
                <?php } ?>
            </div>
        </div>

        <!--
            Routes
        -->
        <div class="col-9">
            <?php foreach ($data as $groupName => $routes) { ?>
                <div class="group-item">
                    <?php if (!empty($groupName)) { ?><h5 class="title" id="<?php echo $groupName; ?>"><?php echo $groupName; ?></h5><?php } ?>
                    <div class="accordion shadow-sm rounded mb-3">
                        <?php foreach ($routes as $routeUri => $route) { ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header small">
                                    <button class="accordion-button route collapsed" data-bs-toggle="collapse" data-bs-target="#panel<?php echo $groupName.$routeUri; ?>">
                                        <?php foreach ($route['routerMethod'] as $method) { ?>
                                            <div class="method <?php echo strtolower($method); ?>"><span><?php echo $method; ?></span></div>
                                        <?php } ?>
                                        <span class="uri"><?php echo $route['path']; ?></span>
                                        <span class="desc"><?php echo $route['desc']; ?></span>
                                        <span class="ms-auto"></span>
                                        <?php if ($route['requireAuth']) { ?>
                                            <div class="method clear">
                                                <a class="auth bg-warning" data-bs-toggle="tooltip" title="Require Authentication"><i class="fa-solid fa-user-shield"></i></a>
                                            </div>
                                        <?php } ?>
                                        <span><?php echo $route['controllerResponseType']; ?></span>
                                    </button>
                                </h3>

                                <div id="panel<?php echo $groupName.$routeUri; ?>" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="accordion">
                                            <!--Controller-->
                                            <?php if ($this->bag->get('kernel.environment') === 'dev') { ?>
                                                <p class="alert bg-light mb-0"><b>Controller: </b> <a href="phpstorm://open?file=<?php echo $this->bag->get('kernel.project_dir') . $route['controllerPath']; ?>&line=<?php echo $route['controllerLine']; ?>"><?php echo $route['controller']; ?></a></p>
                                            <?php } ?>
                                            <!--Attributes-->
                                            <?php if (!empty($route['routerAttr'])) { ?>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center fw-bold mb-2 pb-2 border-bottom border-info pointer" data-bs-toggle="collapse" data-bs-target="#panelAttr<?php echo $groupName.$routeUri; ?>">
                                                        <i class="fa-solid fa-code me-2"></i>
                                                        Route Attributes
                                                    </div>
                                                    <div id="panelAttr<?php echo $groupName.$routeUri; ?>" class="accordion-collapse collapse">
                                                        <?php foreach ($route['routerAttr'] as $name => $type) { ?>
                                                            <p class="mb-0"><span class="d-inline-block" style="min-width: 30%;"><?php echo $name; ?>:</span><code><?php echo $type; ?></code></p>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <!--Header-->
                                            <?php if (!empty($route['header'])) { ?>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center fw-bold mb-2 pb-2 border-bottom border-info pointer" data-bs-toggle="collapse" data-bs-target="#panelHeader<?php echo $groupName.$routeUri; ?>">
                                                        <i class="fa-solid fa-shield me-2"></i> Header Parameters
                                                        <span class="ms-auto bg-secondary text-white px-1 rounded-1 small">HEADER</span>
                                                    </div>
                                                    <div id="panelHeader<?php echo $groupName.$routeUri; ?>" class="accordion-collapse collapse">
                                                        <?php foreach ($route['header'] as $name => $type) { ?>
                                                            <p class="mb-0">
                                                                <span class="d-inline-block" style="min-width: 30%;"><?php echo $name; ?>:</span>
                                                                <code><?php echo $type; ?></code>
                                                            </p>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <!--Query-->
                                            <?php if (!empty($route['query'])) { ?>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center fw-bold mb-2 pb-2 border-bottom border-info pointer" data-bs-toggle="collapse" data-bs-target="#panelQuery<?php echo $groupName.$routeUri; ?>">
                                                        <i class="fa-solid fa-filter me-2"></i> Query Parameters
                                                        <span class="ms-auto bg-secondary text-white px-1 rounded-1 small">GET</span>
                                                    </div>
                                                    <div id="panelQuery<?php echo $groupName.$routeUri; ?>" class="accordion-collapse collapse">
                                                        <?php foreach ($route['query'] as $name => $type) { ?>
                                                            <div class="mb-1 d-flex">
                                                                <span style="min-width: 20%;"><?php echo $name; ?>:</span>
                                                                <?php if (is_array($type)) { ?>
                                                                    <div class="d-flex flex-column flex-fill">
                                                                        <code><pre class="mb-0"><?php echo json_encode($type, JSON_PRETTY_PRINT); ?></pre></code>
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <code><?php echo $type; ?></code>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <!--Body-->
                                            <?php if (!empty($route['request'])) { ?>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center fw-bold mb-2 pb-2 border-bottom border-info pointer" data-bs-toggle="collapse" data-bs-target="#panelBody<?php echo $groupName.$routeUri; ?>">
                                                        <i class="fa-solid fa-database me-2"></i> Body Parameters
                                                        <span class="ms-auto bg-secondary text-white px-1 rounded-1 small"><?php echo implode('|', array_diff($route['routerMethod'], ['GET'])); ?></span>
                                                    </div>
                                                    <div id="panelBody<?php echo $groupName.$routeUri; ?>" class="accordion-collapse collapse">
                                                        <?php foreach ($route['request'] as $name => $type) { ?>
                                                            <div class="mb-0 d-flex">
                                                                <span class="d-inline-block" style="min-width: 30%;"><?php echo $name; ?>:</span>
                                                                <?php if (is_array($type)) { ?>
                                                                    <code><pre class="mb-0"><?php echo json_encode($type, JSON_PRETTY_PRINT); ?></pre></code>
                                                                <?php } else { ?>
                                                                    <code><?php echo $type; ?></code>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <!--Response-->
                                            <?php if (!empty($route['response'])) { ?>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center fw-bold mb-2 pb-2 border-bottom border-success pointer" data-bs-toggle="collapse" data-bs-target="#panelResponse<?php echo $groupName.$routeUri; ?>">
                                                        <i class="fa-solid fa-reply me-2"></i> Response
                                                        <span class="ms-auto bg-secondary text-white px-1 rounded-1 small"><?php echo 'ApiResponse' === $route['controllerResponseType'] ? 'JSON' : $route['controllerResponseType']; ?></span>
                                                    </div>
                                                    <div id="panelResponse<?php echo $groupName.$routeUri; ?>" class="accordion-collapse collapse">
                                                        <?php foreach ($route['response'] as $code => $response) { ?>
                                                            <div class="mb-0 d-flex">
                                                                <span class="d-inline-block" style="min-width: 30%;"><?php echo($statusText[$response['code'] ?? $code] ?? '').' '.($response['code'] ?? $code); ?>:</span>
                                                                <code><pre class="mb-0"><?php echo json_encode($response, JSON_PRETTY_PRINT); ?></pre></code>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</main>
</body>
</html>