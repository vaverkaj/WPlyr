class WPlyr_video extends wp.element.Component {
  state = {
    repo: null
  };

  render() {
    return React.createElement("div", {
      className: "container"
    }, React.createElement("div", {
      className: "row mt-5"
    }, React.createElement("div", {
      className: "col-md-10 offset-md-1 align-middle p-5 rounded",
      style: {
        backgroundColor: 'deepskyblue'
      }
    }, React.createElement("div", {
      className: "video-container",
      id: "container"
    }, React.createElement("video", {
      controls: true,
      crossOrigin: true,
      playsInline: true,
      poster: "media/thumb.jpeg",
      id: "player"
    }, React.createElement("source", {
      src: "https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4",
      type: "video/mp4",
      size: "576"
    }), React.createElement("source", {
      src: "player/media/toystory_lowframe.mp4",
      type: "video/mp4",
      size: 576
    }), React.createElement("source", {
      src: "player/media/toystory.mp4",
      type: "video/mp4",
      size: 720
    }), React.createElement("source", {
      src: "player/media/toystory.webm",
      type: "video/webm",
      size: 720
    }))))));
  }

}