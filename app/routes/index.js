var express = require('express');
var router = express.Router();
var PodioJS = require('../../lib/podio-js');
var sessionStore = require('../sessionStore');
var Busboy = require("busboy");
var temp = require('temp');
var fs = require('fs');

// Remember to place a file in this folder called 'config.json',
// with the contents formatted like so:
// {
//   "clientId": "<Your client ID>",
//   "clientSecret": "<Your client secret>""
// }
var config = JSON.parse(fs.readFileSync('./config.json'));

var clientId = config.clientId;
var clientSecret = config.clientSecret;

var podio = new PodioJS({ authType: 'server', clientId: clientId, clientSecret: clientSecret }, { sessionStore: sessionStore });

function getFullURL(req) {
  return req.protocol + '://' + req.get('host') + '/';
}

function getDate(){

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!

    var yyyy = today.getFullYear();
    if(dd<10){
        dd='0'+dd
    }
    if(mm<10){
        mm='0'+mm
    }
    return yyyy+'-'+mm+'-'+dd;
}

/* GET home page. */
router.get('/', function(req, res) {
    var authCode = req.query.code;
    var errorCode = req.query.error;
    var redirectURL = getFullURL(req);

    if (typeof authCode !== 'undefined') {
        podio.isAuthenticated()
            .then(function(responseData) {
                res.redirect('/form');
            })
            .catch(function () {

                if (typeof authCode !== 'undefined') {
                    podio.getAccessToken(authCode, redirectURL, function (err) {
                        // we are ready to make API calls
                        res.redirect('/form');
                    });
                } else if (typeof errorCode !== 'undefined') {
                    // an error occured
                    res.render('error', {description: req.query.error_description});
                } else {
                    // we have neither an authCode nor have we authenticated before
                    res.render('index', {authUrl: podio.getAuthorizationURL(redirectURL)});
                }
            });
    } else {
        res.render('splash');
    }
});

router.get('/chk', function(req, res) {
    var authCode = req.query.code;
    var errorCode = req.query.error;
    var redirectURL = getFullURL(req);


    podio.isAuthenticated()
        .then(function () {
            // ready to make API calls
            res.redirect('/form');
        })
        .catch(function () {

            if (typeof authCode !== 'undefined') {
                podio.getAccessToken(authCode, redirectURL, function (err) {
                    // we are ready to make API calls
                    res.redirect('/form');
                });
            } else if (typeof errorCode !== 'undefined') {
                // an error occured
                res.render('error', { description: req.query.error_description });
            } else {
                // we have neither an authCode nor have we authenticated before
                res.render('index', { authUrl: podio.getAuthorizationURL(redirectURL) });
            }
        });
});

router.get('/form', function(req, res) {
    var clock_app_id = 15691145;
    var user;
    var options;

    podio.isAuthenticated()
        .then(function() {
            return podio.request('get', '/user/status');
        })
        .then(function(responseData) {
            user = responseData.profile;
        })
        .then(function() {
            options = {
                "limit" : 2,
                "filters" : {
                    "created_by": {"type": "user", "id": user.profile_id},
                    "created_on": {"from": getDate()}
                }
            };

            return podio.request('post', '/item/app/'+clock_app_id+'/filter/', options);
        })
        .then(function(responseData2) {
            res.render('form', { response: JSON.stringify(responseData2.items )});
        })
        .catch(function(err) {
            res.send(401);
        });
    return;

    /*var app_id = 5939966;
    var options = {
        "limit": 3,
        "sort_by": "date"
    };

    podio.isAuthenticated()
        .then(function() {
            return podio.request('post', '/item/app/'+app_id+'/filter/', options);
        })
        .then(function(responseData) {
            console.log(responseData);
            res.render('form', { response: JSON.stringify(responseData.items )});
        })
        .catch(function(err) {
            res.send(401);
        });*/
});

/*router.get('/user', function(req, res) {
    console.log("user " + res);
  podio.isAuthenticated()
  .then(function() {
    return podio.request('get', '/user/status');
  })
  .then(function(responseData) {
    res.render('user', { profile: responseData.profile });
  })
  .catch(function(err) {
    res.send(401);
  });
});*/

/*router.get('/upload', function(req, res) {
  res.render('upload');
});

router.post('/upload', function(req, res) {
  var busboy = new Busboy({ headers: req.headers });

  podio.isAuthenticated()
  .then(function() {

    busboy.on('file', function(fieldname, file, filename, encoding, mimetype) {

      var dir = temp.mkdirSync();
      var filePath = dir + '/' + filename;

      fs.writeFileSync(filePath, '');

      file.on('data', function(data) {
        fs.appendFileSync(filePath, data);
      });

      file.on('end', function() {
        podio.uploadFile(filePath, filename)
        .then(function(body, response) {
          res.render('upload_success', { fileId: body.file_id })
        })
        .catch(function (err) {
          res.end(String(err));
        });
      });
    });
    req.pipe(busboy);
  })
  .catch(function () {
    res.send(401);
  });
});*/

module.exports = router;
