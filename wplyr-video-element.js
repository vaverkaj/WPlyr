class WPlyr_video extends wp.element.Component {
  state = {
    //source: "https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4"
  };
  render() {
    return React.createElement("div", {
    }, React.createElement("div", {
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