(function (blocks, editor, element, components) {

    blocks.registerBlockType('wplyr-better-video/wplyr-video-block', {
        title: 'WPlyr video',
        icon: 'format-video',
        category: 'embed',
        attributes: {
            content: {
                type: 'string',
            },
        },
        edit:
            function (props) {
                if(typeof props.attributes.videosArray === 'undefined'){
                    props.attributes.videosArray = [];
                } 
                function onChangeAlignment(newAlignment) {
                    alert("changed");
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
                                    value: props.attributes.content,
                                    onChange: (value) => {
                                        props.setAttributes({ content: value });
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
                                            console.log(props.attributes.videosArray);
                                            
                                    }
                                }, 'Search'
                            ),element.createElement("ul", null, props.attributes.videosArray.map(function (array, index) {
                                return element.createElement("li", {
                                  key: index
                                }, array['post_title']);
                              }))
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
