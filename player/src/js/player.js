import Plyr from 'plyr';
import alertify from 'alertifyjs';

(() => {
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const selector = '.wplyr_player';
            const containers = document.getElementsByClassName('wplyr_container');

            if (window.shr) {
                window.shr.setup({
                    count: {
                        classname: 'button__count',
                    },
                });
            }

            // Setup tab focus
            const tabClassName = 'tab-focus';

            // Remove class on blur
            document.addEventListener('focusout', event => {
                var anyContainerContains = false;
                for (var i = 0; i < containers.length; ++i) {
                    var item = containers[i];
                    if (item.contains(event.target)) {
                        anyContainerContains = true;
                        continue
                    }
                }
                if (!event.target.classList || anyContainerContains) {
                    return;
                }

                event.target.classList.remove(tabClassName);
            });

            // Add classname to tabbed elements
            document.addEventListener('keydown', event => {
                if (event.keyCode !== 9) {
                    return;
                }

                // Delay the adding of classname until the focus has changed
                // This event fires before the focusin event
                setTimeout(() => {
                    const focused = document.activeElement;

                    var anyContainerFocused = false;
                    for (var i = 0; i < containers.length; ++i) {
                        var item = containers[i];
                        if (item.contains(focused)) {
                            anyContainerContains = true;
                            continue
                        }
                    }

                    if (!focused || !focused.classList || anyContainerFocused) {
                        return;
                    }

                    focused.classList.add(tabClassName);
                }, 10);
            });

            const players = Array.from(document.querySelectorAll(selector)).map(p => new Plyr(p, {
                debug: true,
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', /*'captions', 'settings',*/ 'airplay', 'fullscreen'],
            }));

            // Expose for tinkering in the console
            window.players = players;

            for (let i = 0; i < players.length; i++) {
                const player = players[i];
                player.once('canplay', () => player.currentTime = 0);
                player.sourceIndex = -1;
                for (let j = 0; j < player.elements.original.classList.length; j++) {
                    var classString = player.elements.original.classList[j];
                    if (/wplyr_video_\d+/.test(classString)) {
                        var string = classString.replace("wplyr_video_", "");
                        player.videoId = parseInt(string, 10);
                    }
                }
                nextSource(player)();
                player.on('ended', nextSource(player));
                player.controls;
            }


            // Setup type toggle
            const buttons = document.querySelectorAll('[data-source]');
            const types = {
                video: 'video',
                audio: 'audio',
                youtube: 'youtube',
                vimeo: 'vimeo',
            };
            let currentType = window.location.hash.replace('#', '');
            const historySupport = window.history && window.history.pushState;

            // Toggle class on an element
            function toggleClass(element, className, state) {
                if (element) {
                    element.classList[state ? 'add' : 'remove'](className);
                }
            }

            // Set a new source
            function newSource(type, init) {
                // Bail if new type isn't known, it's the current type, or current type is empty (video is default) and new type is video
                if (!(type in types) ||
                    (!init && type === currentType) ||
                    (!currentType.length && type === types.video)
                ) {
                    return;
                }

                // Set the current type for next time
                currentType = type;

                // Remove active classes
                Array.from(buttons).forEach(button => toggleClass(button.parentElement, 'active', false));

                // Set active on parent
                toggleClass(document.querySelector(`[data-source="${type}"]`), 'active', true);

                // Show cite
                Array.from(document.querySelectorAll('.plyr__cite')).forEach(cite => {
                    cite.setAttribute('hidden', '');
                });
                document.querySelector(`.plyr__cite--${type}`).removeAttribute('hidden');
            }

            // Bind to each button
            Array.from(buttons).forEach(button => {
                button.addEventListener('click', () => {
                    const type = button.getAttribute('data-source');

                    newSource(type);

                    if (historySupport) {
                        window.history.pushState({ type }, '', `#${type}`);
                    }
                });
            });

            // List for backwards/forwards
            window.addEventListener('popstate', event => {
                if (event.state && 'type' in event.state) {
                    newSource(event.state.type);
                }
            });

            // On load
            if (historySupport) {
                const video = !currentType.length;

                // If there's no current type set, assume video
                if (video) {
                    currentType = types.video;
                }

                // Replace current history state
                if (currentType in types) {
                    window.history.replaceState({
                            type: currentType,
                        },
                        '',
                        video ? '' : `#${currentType}`,
                    );
                }

                // If it's not video, load the source
                if (currentType !== types.video) {
                    newSource(currentType, true);
                }
            }

            function isEmpty(str) {
                return (!str || 0 === str.length);
            }

            //This function switches individual videos in the playlist
            function nextSource(player) {
                return function() {
                    if (player.currentTime > 0 || player.sourceIndex == -1) {

                        if (typeof window.videoSourceMap[player.videoId] !== 'undefined' && window.videoSourceMap[player.videoId].length > player.sourceIndex) {
                            do {
                                console.log("do");
                                player.sourceIndex++;
                            } while (typeof window.videoSourceMap[player.videoId][player.sourceIndex] !== 'undefined' && isEmpty(window.videoSourceMap[player.videoId][player.sourceIndex].source) && window.videoSourceMap[player.videoId].length > player.sourceIndex)
                            if (window.videoSourceMap[player.videoId].length <= player.sourceIndex) {
                                player.sourceIndex = 0;
                            }
                            var source = window.videoSourceMap[player.videoId][player.sourceIndex].source;
                            var type = window.videoSourceMap[player.videoId][player.sourceIndex].type
                            var sources = [];
                            if (type === 'youtube') {
                                sources = [{
                                    src: source,
                                    provider: 'youtube'
                                }];
                            } else if (type === 'video') {
                                sources = [{
                                    src: source,
                                    type: 'video/mp4',
                                }];
                            }

                            player.source = {
                                type: 'video',
                                sources: sources,
                            }
                            if (player.sourceIndex != 0) {
                                var playPromise = player.play();
                                if (playPromise !== undefined) {
                                    playPromise.then(function() {
                                        console.log("Playback started");
                                    }).catch(function(error) {
                                        alertify.error("<b>Video playback might not be working. Please use more modern browser.</b></br>" + error);
                                    });
                                }
                            }
                        }
                    }
                }
            }
        } catch (error) {
            alertify.error("<b>Video playback might not be working. Please use more modern browser.</b></br>" + error);
            throw error;
        }
    });

})();