window.addEventListener("load", () => {

  const tl = gsap.timeline();

  tl.from(".opening-text p", {
    opacity: 0,
    y: 20,
    duration: 1
  })
    .from(".opening-text h1", {
      opacity: 0,
      y: 30,
      duration: 1
    })
    .to("#opening", {
      opacity: 0,
      duration: 1.5,
      delay: 1.2
    })
    .set("#opening", { display: "none" })
    .to("#main-content", {
      opacity: 1,
      duration: 1.5
    });

});