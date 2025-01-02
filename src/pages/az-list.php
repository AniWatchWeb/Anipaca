<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
session_start();


function fetchApi($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}


$category = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$page = max(1, (int)($_GET['page'] ?? 1));

$letter = ($category === 'az-list') ? '' : str_replace('az-list/', '', $category);


$apiUrl = "$zpi/az-list/{$category}?page={$page}";
$response = fetchApi($apiUrl);
$data = json_decode($response, true);

if (!$response || !$data['success'] ?? false) {
    $apiUrl = "$zpi/{$category}?page={$page}";
    $response = fetchApi($apiUrl);
    $data = json_decode($response, true);
}

$errorMessage = null;
$azResults = $data['results']['data'] ?? null;
$totalPages = $azResults ? ceil(count($azResults) / 20) : 0;


if (!$azResults) {
    $errorMessage = $response === false ? 'Could not connect to the API.' : 'Failed to fetch popular anime. Please try again later.';
}

?>


<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>List All <?= str_replace('-', ' ', ucfirst($category)) ?> Anime on <?=$websiteTitle?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="List All <?= str_replace('-', ' ', ucfirst($category)) ?> Anime on <?=$websiteTitle?>">
    <meta name="description" content="Popular Anime in HD with No Ads. Watch anime online">
    <meta name="keywords"
        content="<?=$websiteTitle?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, <?=$websiteTitle?>, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa">
    <meta name="charset" content="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="noindex, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Language" content="en">
    <meta property="og:title" content="List All <?= str_replace('-', ' ', ucfirst($category)) ?> Anime on <?=$websiteTitle?>">
    <meta property="og:description"
        content="List All <?= str_replace('-', ' ', ucfirst($category)) ?> Anime on <?=$websiteTitle?> in HD with No Ads. Watch anime online">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?=$websiteTitle?>">
    <meta itemprop="image" content="<?=$banner?>">
    <meta property="og:image" content="<?=$banner?>">
    <meta property="og:image:width" content="650">
    <meta property="og:image:height" content="350">
    <meta property="twitter:card" content="summary">
    <meta name="apple-mobile-web-app-status-bar" content="#202125">
    <meta name="theme-color" content="#202125">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css?v=<?=$version?>"
        type="text/css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css?v=<?=$version?>"
        type="text/css">
    <link rel="apple-touch-icon" href="<?=$websiteUrl?>/public/favicon.png?v=<?=$version?>" />
    <link rel="shortcut icon" href="<?=$websiteUrl?>/public/favicon.png?v=<?=$version?>" type="image/x-icon"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$websiteUrl?>/public/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$websiteUrl?>/public/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=$websiteUrl?>/public/favicon-16x16.png">
    <link rel="mask-icon" href="<?=$websiteUrl?>/public/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="icon" sizes="192x192" href="<?=$websiteUrl?>/src/assets/images/touch-icon-192x192.png?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/src/assets/css/styles.min.css?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/src/assets/css/min.css?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/src/assets/css/new.css?v=<?=$version?>">
    <script>
    setTimeout(function() {
    const cssFiles = [
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css'
    ];
    const firstLink = document.getElementsByTagName('link')[0];
    cssFiles.forEach(file => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `${file}?v=<?=$version?>`;
        link.type = 'text/css';
        firstLink.parentNode.insertBefore(link, firstLink);
                });
        }, 500);
    </script>

    <noscript>
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css?v=<?=$version?>" />
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css?v=<?=$version?>" />
    </noscript>
    <script></script>
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">


    <link rel="stylesheet" href="<?=$websiteUrl?>/src/assets/css/search.css">
    <script src="<?=$websiteUrl?>/src/assets/js/search.js"></script>

</head>

<body data-page="page_anime">
    <div id="sidebar_menu_bg"></div>
    <div id="wrapper" data-page="page_home">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/src/component/header.php'); ?>
        <div class="clearfix"></div>
        <div id="main-wrapper" class="layout-page page-az">
            <div class="container">
                <div class="prebreadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/home" title="Home">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">A-Z List</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-az-wrap">
                    <!--Begin: Section film list-->
                    <section class="block_area block_area_category">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">Sort By Letters</h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="main-az">
                            <ul class="ulclear az-list">
                                <li class="<?= empty($letter) ? 'active' : '' ?>"><a href="/src/pages/az-list.php">All</a></li>                               
                                <li class="<?= $letter === '0-9' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/0-9">0-9</a></li>
                                <li class="<?= $letter === 'a' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/a">A</a></li>
                                <li class="<?= $letter === 'b' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/b">B</a></li>
                                <li class="<?= $letter === 'c' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/c">C</a></li>
                                <li class="<?= $letter === 'd' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/d">D</a></li>
                                <li class="<?= $letter === 'e' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/e">E</a></li>
                                <li class="<?= $letter === 'f' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/f">F</a></li>
                                <li class="<?= $letter === 'g' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/g">G</a></li>
                                <li class="<?= $letter === 'h' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/h">H</a></li>
                                <li class="<?= $letter === 'i' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/i">I</a></li>
                                <li class="<?= $letter === 'j' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/j">J</a></li>
                                <li class="<?= $letter === 'k' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/k">K</a></li>
                                <li class="<?= $letter === 'l' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/l">L</a></li>
                                <li class="<?= $letter === 'm' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/m">M</a></li>
                                <li class="<?= $letter === 'n' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/n">N</a></li>
                                <li class="<?= $letter === 'o' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/o">O</a></li>
                                <li class="<?= $letter === 'p' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/p">P</a></li>
                                <li class="<?= $letter === 'q' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/q">Q</a></li>
                                <li class="<?= $letter === 'r' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/r">R</a></li>
                                <li class="<?= $letter === 's' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/s">S</a></li>
                                <li class="<?= $letter === 't' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/t">T</a></li>
                                <li class="<?= $letter === 'u' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/u">U</a></li>
                                <li class="<?= $letter === 'v' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/v">V</a></li>
                                <li class="<?= $letter === 'w' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/w">W</a></li>
                                <li class="<?= $letter === 'x' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/x">X</a></li>
                                <li class="<?= $letter === 'y' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/y">Y</a></li>
                                <li class="<?= $letter === 'z' ? 'active' : '' ?>"><a href="/src/pages/az-list.php/z">Z</a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>

    <div class="tab-content">
        <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
            <div class="film_list-wrap">
                <?php if (!empty($azResults)): ?>
                    <?php foreach ($azResults as $anime): ?>
                        <div class="flw-item">
                            <div class="film-poster">
                                <?php if (!empty($anime['adultContent'])): ?>
                                    <div class="tick ltr" style="position: absolute; top: 10px; left: 10px;">
                                        <div class="tick-item tick-age amp-algn">18+</div>
                                    </div>
                                <?php endif; ?>
                                <div class="tick ltr" style="position: absolute; bottom: 10px; left: 10px;">
                                    <?php if (!empty($anime['tvInfo']['sub'])): ?>
                                        <div class="tick-item tick-sub amp-algn" style="text-align: left;">
                                            <i class="fas fa-closed-captioning"></i> &nbsp;<?= $anime['tvInfo']['sub'] ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($anime['tvInfo']['dub'])): ?>
                                        <div class="tick-item tick-dub amp-algn" style="text-align: left;">
                                            <i class="fas fa-microphone"></i> &nbsp;<?= $anime['tvInfo']['dub'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <img class="film-poster-img lazyload" 
                                     data-src="<?= $anime['poster'] ?>" 
                                     src="<?= $anime['poster'] ?>" 
                                     alt="<?= htmlspecialchars($anime['title']) ?>">
                                <a class="film-poster-ahref" href="/details/<?= $anime['id'] ?>"></a>
                            </div>
                            <div class="film-detail">
                                <h3 class="film-name">
                                    <a href="/details/<?= $anime['id'] ?>" 
                                       title="<?= htmlspecialchars($anime['title']) ?>">
                                       <?= htmlspecialchars($anime['title']) ?>
                                    </a>
                                </h3>
                                <div class="fd-infor">
                                    <span class="fdi-item"><?= htmlspecialchars($anime ['tvInfo'] ['showType']) ?></span>
                                    <span class="dot"></span>
                                    <span class="fdi-item"><?= htmlspecialchars($anime ['tvInfo'] ['duration']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="clearfix"></div>

                   
                <?php else: ?>
                    <p>No results found.</p>
                <?php endif; ?>
            </div>
             <!-- Pagination -->
             <?php if (!empty($azResults) && $totalPages > 0): ?>
             <div class="pre-pagination mt-5 mb-5">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-lg justify-content-center">
                                <?php for ($i = 1; $i <= min(3, $totalPages); $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?page=<?= $i ?>" 
                                           title="Page <?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?page=<?= $currentPage + 1 ?>" 
                                           title="Next">›</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($totalPages > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?page=<?= $totalPages ?>" 
                                           title="Last">»</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
             <?php endif; ?>
                    <div class="clearfix"></div>
        </div>
    </div>
</section>


                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/src/component/footer.php'); ?>
        <div id="mask-overlay"></div>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

        <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js">
        </script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
        <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/app.js"></script>
        <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/comman.js"></script>
        <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/movie.js"></script>
        <link rel="stylesheet" href="<?= $websiteUrl ?>/src/assets/css/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/function.js"></script>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    </div>
</body>

</html>
