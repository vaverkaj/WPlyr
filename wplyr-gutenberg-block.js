( function( blocks, element ) {

    var blockStyle = {
        backgroundColor: '#900',
        color: '#fff',
        padding: '20px',
    };

    blocks.registerBlockType( 'wplyr-better-video/wplyr-video-block', {
        title: 'WPlyr video',
        icon: 'format-video',
        category: 'embed',
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'p',
            },
            alignment: {
                type: 'string',
                default: 'none',
            },
        },
        edit: function() {
            return element.createElement(
                'p',
                { style: blockStyle },
                'Hello World, step 1 (from the editor).'
            );
        },
        save: function() {
            return  element.createElement(WPlyr_video);
        },
    } );
}(
    window.wp.blocks,
    window.wp.element
)

);