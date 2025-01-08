<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

$id = $_GET['id'];
$server = $_GET['server'] ?? 'hd-1';
$useProxy = $server !== 'hd-1';
$isIframe = isset($_GET['embed']) && $_GET['embed'] === 'true';

$categories = ["sub", "raw"];
$data = null;

foreach ($categories as $category) {
    $api_url = "$api/episode/sources?animeEpisodeId=$id&server=$server&category=$category";
    $response = file_get_contents($api_url);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            break; // Stop if a successful response is found
        }
    }
}

if (!$data || !$data['success']) {
    die("Error: Unable to fetch episode sources.");
}

// Process $data here as needed

$m3u8_url = $data['data']['sources'][0]['url'];
$video_url = $useProxy ? $proxy . $m3u8_url : $m3u8_url;
$intro_start = $data['data']['intro']['start'];
$intro_end = $data['data']['intro']['end'];
$outro_start = $data['data']['outro']['start'];
$outro_end = $data['data']['outro']['end'];
$thumbnail_url = null;
$subtitles = [];

foreach ($data['data']['tracks'] as $track) {
    if ($track['kind'] === 'thumbnails') {
        $thumbnail_url = $track['file'];
    } elseif ($track['kind'] === 'captions') {
        $isEnglish = stripos($track['label'], 'English') !== false || 
                     stripos($track['label'], 'eng') !== false;
        $subtitles[] = [
            'label' => $track['label'],
            'file' => $track['file'],
            'default' => $isEnglish
        ];
    }
}

if ($isIframe) {
    ?>
    <div class="artplayer-app"></div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="js/artplayer.js"></script>
    <script src="js/hls.js"></script>
    <script src="js/artplayer-plugin-chromecast.js"></script>
    <script src="js/artplayer-plugin-dash-control.js"></script>
 
    <script src="js/artplayer-plugin-hls-control.js"></script>
    <script src="js/artplayer-plugin-chapter.js"></script>
    <link rel="stylesheet" href="css/artplayer.css">
   
    <script>
        const settings = {
            autoSkipIntro: true,
            autoSkipOutro: true
        };

        function playM3u8(video, url, art) {
            if (Hls.isSupported()) {
                if (art.hls) art.hls.destroy();
                const hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(video);
                art.hls = hls;
                art.on('destroy', () => hls.destroy());
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = url;
            } else {
                art.notice.show = 'Unsupported playback format: m3u8';
            }
        }

        const art = new Artplayer({
            container: '.artplayer-app',
            theme: '#ff9a68',
            url: '<?= $video_url ?>',
            type: 'm3u8',
            customType: {
                m3u8: playM3u8,
            },
            icons: {
                loading: '<img src="icons/loading.svg">',
                state: '<img src="icons/state.svg">',
                indicator: '<img width="16" height="16" src="icons/indicator.svg">',
                setting: '<img src="icons/setting.svg">',
                pip: '<img src="icons/pip.svg">',
                fullscreenOn: '<img src="icons/fs-on.svg">',
                fullscreenOff: '<img src="icons/fs-on.svg">',
                volume: '<img src="icons/volumee.svg">',
                volumeClose: '<img src="icons/vl-close.svg">',
           
            },
            volume: 0.5,
            autoplay: true,
            pip: true,
            autoSize: false,
            autoMini: false,
            screenshot: false,
            setting: true,
            playbackRate: true,
            aspectRatio: true,
            fullscreen: true,
            autoOrientation: true,
            fullscreenWeb: false,
            lock: true,
            miniProgressBar: false,
            backdrop: true,
            playsInline: true,
            autoPlayback: true,
            airplay: true,
            screenshot: true,
            theme: '#ff545c',
            lang: navigator.language.toLowerCase(),
            moreVideoAttr: {
                crossOrigin: 'anonymous',
            },
            settings: [
                {
                    html: 'Subtitle',
                    tooltip: 'Choose Subtitle',
                    icon: '<img width="22" height="22" src="icons/subtitle.svg">',
                    selector: [
                        <?php foreach ($subtitles as $subtitle): ?> {
                                html: '<?= $subtitle['label'] ?>',
                                url: '<?= $subtitle['file'] ?>',
                                default: <?= $subtitle['default'] ? 'true' : 'false' ?>,
                                click: function() {
                                    art.subtitle.url = '<?= $subtitle['default'] ? 'true' : 'false' ?>';
                                }
                            },
                        <?php endforeach; ?>
                    ],
                    onSelect: function(item) {
                        art.subtitle.url = item.url;
                        return item.html;
                    },
                },
                {
                    html: 'Auto Skip',
                    tooltip: localStorage.getItem('autoSkipEnabled') === 'true' ? '' : '',
                    icon: '<img width="22" height="22" src="icons/skip.svg">',
                    switch: true,
                    default: true,
                    onSwitch: function(item) {
                        const isEnabled = !settings.autoSkipIntro;
                        settings.autoSkipIntro = isEnabled;
                        settings.autoSkipOutro = isEnabled;
                        item.tooltip = isEnabled ? '' : '';
                        localStorage.setItem('autoSkipEnabled', isEnabled);
                        return isEnabled;
                    },
                }
            ],
            plugins: [

                artplayerPluginHlsControl({
                    quality: {
                        control: false,
                        setting: true,
                        title: 'Quality',
                        auto: 'Auto',
                    },
                }),
               
                artplayerPluginChapter({
                    chapters: [
                        {
                            start: <?= $intro_start ?>,
                            end: <?= $intro_end ?>,
                            title: 'Intro',
                            color: '#fdd253', // Changed color to match the new style
                            style: {
                                transform: 'scaleY(0.6)', // Added transform style
                            },
                        },
                        {
                            start: <?= $outro_start ?>,
                            end: <?= $outro_end ?>,
                            title: 'Outro',
                            color: 'red',
                        },
                    ],
                }),
               
                artplayerPluginChromecast({
                    media: {
                        type: 'application/x-mpegURL',
                        title: 'HLS Stream',
                        src: '<?= $proxy ?><?= $m3u8_url ?>'
                    }
                }),
            ],

           
        });

        const fastForwardLayer = art.layers.add({
            html: '<svg viewBox="-5 -10 75 75" xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="white"><path d="M29.92 45H25.21V26.54l-4.6 2.78v-3.92l8.81-3.6h0V45zm18.18-10c0 3.34-.61 5.9-1.83 7.67-1.21 1.77-2.94 2.66-5.19 2.66-2.23 0-3.95-.86-5.17-2.6-1.21-1.77-1.84-4.24-1.84-7.55v-4.56c0-3.34.6-5.9 1.83-7.67 1.21-1.77 2.94-2.66 5.19-2.66 2.23 0 3.95.86 5.17 2.6 1.21 1.77 1.84 4.24 1.84 7.55v4.56zm-4.71-4.9c0-1.9-.19-3.32-.57-4.25-.38-.93-.97-1.47-1.74-1.47-1.49 0-2.27 1.74-2.27 4.17v5.63c0 1.95.19 3.32.57 4.25.38.93.97 1.47 1.74 1.47 1.49 0 2.27-1.74 2.27-4.17v-5.63z"/><path d="M40.01 5.45V0l10 7.79-10 7.79V10.3H4.91v29.85H18.76v4.85H0V5.45h40.01z"/></svg>',
            style: {
                position: 'absolute', 
                top: '49%', 
                left: '75%', 
                transform: 'translate(-50%, -50%)',
                background: '#00000000', 
                padding: '5px 10px',   
                cursor: 'pointer',
                display: 'none',
                fontSize: '1.15rem' 
            },
            click: function() {
                art.currentTime += 10; 
            }
        });

        const skipIntroLayer = art.layers.add({
            html: 'Skip intro',
            style: {
                position: 'absolute',
                border: '2px solid #fff',
                bottom: '62px', 
                right: '10px', 
                background: '#00000000', 
                color: '#fff', 
                padding: '5px 10px', 
                borderRadius: '5px', 
                cursor: 'pointer',
                display: 'none',
                fontSize: '1.15rem' // Initially hidden
            },
            click: function() {
                art.currentTime = <?= $intro_end ?>; // Skip to the end of the intro
            }
        });

        const skipOutroLayer = art.layers.add({
            html: 'Skip outro',
            style: {
                position: 'absolute',
                border: '1px solid #fdd253',
                bottom: '65px', 
                right: '10px', 
                background: '#00000000', 
                color: '#fdd253', 
                padding: '5px 10px', 
                borderRadius: '5px', 
                cursor: 'pointer',
                display: 'none',
                fontSize: '1.15rem' 
            },
            click: function() {
                art.currentTime = <?= $outro_end ?>; 
            }
        });

        art.on('video:timeupdate', () => {
            const currentTime = art.currentTime;
            const skipElement = art.controls['skip'];
            if (skipElement) {
                if ((currentTime >= <?= $intro_start ?> && currentTime < <?= $intro_end ?>) ||
                    (currentTime >= <?= $outro_start ?> && currentTime < <?= $outro_end ?>)) {
                    skipElement.style.display = 'block';
                    if (settings.autoSkipIntro && currentTime < <?= $intro_end ?>) {
                        art.currentTime = <?= $intro_end ?>; // Skip to the end of the intro
                    } else if (settings.autoSkipOutro && currentTime >= <?= $outro_start ?>) {
                        art.currentTime = <?= $outro_end ?>; // Skip to the end of the outro
                    }
                } else {
                    skipElement.style.display = 'none';
                }
            }
        });

        art.controls.add({
            name: 'fast-forward',
            index: 10,
            position: 'right', 
            html: '<svg viewBox="-5 -10 75 75" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white"><path d="M29.92 45H25.21V26.54l-4.6 2.78v-3.92l8.81-3.6h0V45zm18.18-10c0 3.34-.61 5.9-1.83 7.67-1.21 1.77-2.94 2.66-5.19 2.66-2.23 0-3.95-.86-5.17-2.6-1.21-1.77-1.84-4.24-1.84-7.55v-4.56c0-3.34.6-5.9 1.83-7.67 1.21-1.77 2.94-2.66 5.19-2.66 2.23 0 3.95.86 5.17 2.6 1.21 1.77 1.84 4.24 1.84 7.55v4.56zm-4.71-4.9c0-1.9-.19-3.32-.57-4.25-.38-.93-.97-1.47-1.74-1.47-1.49 0-2.27 1.74-2.27 4.17v5.63c0 1.95.19 3.32.57 4.25.38.93.97 1.47 1.74 1.47 1.49 0 2.27-1.74 2.27-4.17v-5.63z"/><path d="M40.01 5.45V0l10 7.79-10 7.79V10.3H4.91v29.85H18.76v4.85H0V5.45h40.01z"/></svg>', 
            tooltip: 'Fast Forward 10 seconds',
            style: {
                color: 'red',
               
            },
            click: function() {
                art.currentTime += 10;
            },
            mounted: function(...args) {
                console.info('mounted', args);
            },
        });
        art.controls.add({
            name: 'rewind',
              index: 10,
               position: 'right',
              html: '<svg viewBox="-5 -10 75 75" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white"><path d="M11.9199 45H7.20508V26.5391L2.60645 28.3154V24.3975L11.4219 20.7949H11.9199V45ZM30.1013 35.0059C30.1013 38.3483 29.4926 40.9049 28.2751 42.6758C27.0687 44.4466 25.3422 45.332 23.0954 45.332C20.8708 45.332 19.1498 44.4743 17.9323 42.7588C16.726 41.0322 16.1006 38.5641 16.0564 35.3545V30.7891C16.0564 27.4577 16.6596 24.9121 17.8659 23.1523C19.0723 21.3815 20.8044 20.4961 23.0622 20.4961C25.32 20.4961 27.0521 21.3704 28.2585 23.1191C29.4649 24.8678 30.0792 27.3636 30.1013 30.6064V35.0059ZM25.3864 30.1084C25.3864 28.2048 25.1983 26.777 24.822 25.8252C24.4457 24.8734 23.8591 24.3975 23.0622 24.3975C21.5681 24.3975 20.7933 26.1406 20.738 29.627V35.6533C20.738 37.6012 20.9262 39.0511 21.3025 40.0029C21.6898 40.9548 22.2875 41.4307 23.0954 41.4307C23.8591 41.4307 24.4236 40.988 24.7888 40.1025C25.1651 39.2061 25.3643 37.8392 25.3864 36.002V30.1084Z" fill="white"/><path d="M11.9894 5.45398V0L2 7.79529L11.9894 15.5914V10.3033H47.0886V40.1506H33.2442V45H52V5.45398H11.9894Z" fill="white"/></svg>',
              tooltip: 'Rewind 10 seconds',
              style: {
                  color: 'red',
                  
              },
            click: function() {
                art.currentTime -= 10; // Rewind 10 seconds
            },
            mounted: function(...args) {
                console.info('mounted', args);
            },
        });

        art.on('ready', () => {
    art.notice.show = 'Thanks for waiting';
})

art.on('ready', () => {
    const defaultSubtitle = <?= json_encode($subtitles) ?>.find(s => s.default);
    art.subtitle.url = defaultSubtitle ? defaultSubtitle.file : '';
    art.subtitle.html = true;
});
        
        art.on('video:timeupdate', () => {
            const currentTime = art.currentTime;
            if (currentTime >= <?= $intro_start ?> && currentTime < <?= $intro_end ?>) {
                skipIntroLayer.style.display = 'block'; // Show the skip button during intro
                skipOutroLayer.style.display = 'none'; // Hide the outro skip button
                if (settings.autoSkipIntro) {
                    art.currentTime = <?= $intro_end ?>;
                }
            } else if (currentTime >= <?= $outro_start ?> && currentTime < <?= $outro_end ?>) {
                skipOutroLayer.style.display = 'block'; // Show the skip button during outro
                skipIntroLayer.style.display = 'none'; // Hide the intro skip button
                if (settings.autoSkipOutro) {
                    art.currentTime = <?= $outro_end ?>;
                }
            } else {
                skipIntroLayer.style.display = 'none'; // Hide the intro skip button otherwise
                skipOutroLayer.style.display = 'none'; // Hide the outro skip button otherwise
            }
        });

      
        // Add click event for subtitle selection
        art.on('subtitle:change', (item) => {
            console.log('Subtitle changed to:', item.html);
        });
        console.info(art.icons.loading);
        const $rewind = art.layers["rewind"];
      const $forward = art.layers["forward"];

      Artplayer.utils.isMobile &&
        art.proxy($rewind, "dblclick", () => {
          art.currentTime = Math.max(0, art.currentTime - 10);
          art.layers["backwardIcon"].style.opacity = 1;
          setTimeout(() => {
            art.layers["backwardIcon"].style.opacity = 0;
          }, 300);
        });

      Artplayer.utils.isMobile &&
        art.proxy($forward, "dblclick", () => {
          art.currentTime = Math.max(0, art.currentTime + 10);
          art.layers["forwardIcon"].style.opacity = 1;
          setTimeout(() => {
            art.layers["forwardIcon"].style.opacity = 0;
          }, 300);
        });

        art.on('ready', () => {
    const defaultSubtitle = <?= json_encode($subtitles) ?>.find(s => s.default);
    art.subtitle.url = defaultSubtitle ? defaultSubtitle.file : '';
    art.subtitle.html = true; // Ensure HTML rendering is enabled
    art.subtitle.html = true; // Ensure HTML rendering is enabled
});

art.on("resize", () => {
      art.subtitle.style({
        fontSize:
          (art.width > 500 ? art.width * 0.02 : art.width * 0.03) + "px",
      });
    });
        
        art.on('ready', () => {
            const autoSkipEnabled = localStorage.getItem('autoSkipEnabled');
            if (autoSkipEnabled === null) {
                localStorage.setItem('autoSkipEnabled', 'true');
            } else {
                settings.autoSkipIntro = autoSkipEnabled === 'true';
                settings.autoSkipOutro = autoSkipEnabled === 'true';
            }
        });
    </script>
    <?php
    exit;
}

?>
