//Gutenberg block used for serialization of the data saved in editor
class WPlyr_video extends wp.element.Component {
    state = {};
    render() {
        return React.createElement("div", {}, React.createElement("div", {
            className: "video-container",
            id: "container"
        }, React.createElement("video", {
            controls: true,
            crossOrigin: true,
            playsInline: true,
            id: "player"
        })));
    }

}