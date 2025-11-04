
export const server = (done) => {
    app.plugins.browsersync.init({
        proxy: "http://wordpress-dm-chains/",
        notify: false,
        port: 3000,
    });
    done();
};