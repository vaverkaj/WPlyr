( function( blocks,editor, element, components ) {

    blocks.registerBlockType( 'wplyr-better-video/wplyr-video-block', {
        title: 'WPlyr video',
        icon: 'format-video',
        category: 'embed',
        attributes: {
            content: {
                type: 'string',
            }
        },
        edit: 
        function(props) {
            function onChangeAlignment( newAlignment ) {
                alert("changed");
            }
  
            return  [
                element.createElement(
                        editor.InspectorControls,
                        { key: 'controls' },
                            element.createElement( 
                                components.TextareaControl, {
                                    style: {height:250},
                                    label: 'Path to the video:',
                                    value: props.attributes.content,
                                    onChange: ( value ) => {
                                        props.setAttributes( { content: value } );
                                    }
                            }, props.attributes.content )
                    ),
                element.createElement(WPlyr_video)
            ]},
        save: function() {
            return  element.createElement(WPlyr_video);
        },
    } );
}(
    window.wp.blocks,
    window.wp.editor,
    window.wp.element,
    window.wp.components
)

);