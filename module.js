
/////// CALLOUT EXAMPLE #1: Mock Callout SUCCESS

console.log("Loaded jobs app.");




/*
    let mockCallback = (params) => {

        let files = params.getFiles();

        let filesData = [];

        files.forEach(file => {

            let fileData = {

                name: file.name,
                size: file.size,
                ext: file.ext,
                creationDate: file.creationDate
            };

            filesData.push(fileData);
        });

        return {
            appId: params.get('appId'),
            userId: params.get('userId'),
            files: filesData
        };
    };


    let params = function() {

        let file = new File(['Test File'], 'TestFile2.txt', { type: 'text' } );

        let params = new FormData();

        params.set('appId', 'app123');
        params.set('userId', 'user123');
        params.set('files[]', file);
        return params;
    };



    // Callout is an action
    let fileUploadRoute = new Route('/upload', mockCallback);
    let server = new Server();
    server.addRoute(fileUploadRoute);
    let client = new Client();
    let req = new HTTPRequest('/upload', params);
    let resp = client.send(req);


    // Callout mediates the interaction between Client and Server
    let callout = new Callout('/upload', mockCallback);
















    let mockFileUpload = new Callout(null, null, mockCallback);
    let mockFileUpload2 = new Callout(mockResp);
    let fileUpload = new Callout('/upload', { method: 'POST', headers: { 'Content-Type': 'form-encoded' }});

    let promise1 = mockFileUpload.send(params);
    let promise2 = mockFileUpload2.send(params);
    let promise3 = fileUpload.send(params);


    promise1.then(resp => resp.json())
    .then(data => {
        console.log(data);
    });
*/