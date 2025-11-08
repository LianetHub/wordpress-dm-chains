
export const server = (done) => {
    app.plugins.browsersync.init({
        proxy: "localhost:3000",
        notify: false,
        port: 3000,
    });
    done();
};