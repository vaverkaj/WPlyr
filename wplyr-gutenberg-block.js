(function (blocks, editor, element, components) {
    blocks.registerBlockType('wplyr-better-video/wplyr-video-block', {
        title: 'WPlyr video',
        icon: 'format-video',
        category: 'embed',
        attributes: {
            content: {
                type: 'string',
                source: 'meta',
                meta: 'wp_wplyr_meta_block_field',
            },
        },
        edit:
        function (props) {
            console.log(blocks.getChildBlockNames('wplyr-better-video/wplyr-video-block'))
                console.log(JSON.parse(props.attributes.content))
                if(typeof props.attributes.videosArray === 'undefined'){
                    props.attributes.videosArray = [];
                } 
                return [
                    element.createElement(
                        editor.InspectorControls,
                        { key: 'controls' },
                        element.createElement(
                            components.PanelBody, {
                                title: 'Search for video:',
                                // icon:'welcome-widgets-menus',
                                initialOpen: true
                            }, element.createElement(
                                components.TextControl, {
                                    value: JSON.parse(props.attributes.content)[props.clientId],
                                    onChange: (value) => {
                                        var metaObject = JSON.parse(props.attributes.content);
                                        metaObject[props.clientId] = value;
                                        props.setAttributes({ content: JSON.stringify(metaObject) });
                                    }
                                }
                            ), element.createElement(
                                components.Button, {
                                    isDefault: true,
                                    onClick: () => {
                                        fetch('http://localhost/wordpress/wp-json/wplyr/videos')
                                            .then(res => res.json())
                                            .then(data => {
                                                props.setAttributes({videosArray : data});
                                            });                             
                                    }
                                }, 'Search'
                            ),element.createElement("ul", null, props.attributes.videosArray.map(function (array, index) {
                                return element.createElement("li", {
                                  key: index
                                }, array['post_title']);
                              })), element.createElement(
                                components.Button, {
                                    isDefault: true,
                                    onClick: () => {
                                                           
                                    }
                                }, 'Open Search'
                            )
                        )
                    ),
                    element.createElement(WPlyr_video)
                ]
            },
        save: function () {
            return element.createElement(WPlyr_video);
        },
    });
}(
    window.wp.blocks,
    window.wp.editor,
    window.wp.element,
    window.wp.components
)

);
