This library allows you to make HTTP requests using libcurl in PHP. It's designed to use the cURL multi interface to optimize speed and increase download efficiency.

Some use cases include:
- Making API calls with headers include "GET", "POST", "PATCH", "PUT", "DELETE", "OPTIONS", and others
- Downloading large files
- Downloading a large number of files simultaneously

NOTE: This library will download all pages simultaneously. Please DO NOT overload a server by adding many URLs from the same domain all at once. That very easily could git your IP address blocked.

#Overview
The basic process for using this library is:
1. Initialize the Http object.
2. Add your download requests.
3. Run the Http object

When you're done, simply set the Http object to null to clear everything.

Here's a basic example with 2 requests, the first saving to a file and the second storing the result in memory:
```
$httpObj = new Http();
$httpObj->add_get("https://potentpages.com/", "", null, "cookies.txt", false, null, null, 10, 15, "out.html", null);
$httpObj->add_get("https://potentpages.com/test.txt", "", null, "cookies.txt", false, null, null, 10, 15, null, null);
$response = $httpObj->run();
print_r($response);
$httpObj = null;
```

#HTTP Request Parameters
To make a general HTTP request, use the `add` function. The parameters to the `add` function are as follows:
1. URL to be downloaded
2. Referer string
3. User Agent String
4. HTTP Method
	- Default method is "GET"
	- Other options include
		- POST
		- PATCH
		- PUT
		- OPTIONS
5. Post Data (as a raw string)
    - Note that there won't be any variable name by default (e.g. "var=value"). To do this, explicitly specify it.
6. Filename to store your cookies.
    - Note that if this field is null, cookies will not be stored at all
7. Whether or not to clear cookies every time a request is made (true/false)
8. IP Address of HTTP Proxy (null by default)
9. Port of HTTP Proxy (null by default)
10. Connection timeout (in seconds), 3s by default
11. Completion timeout (in seconds) for the entire request to be done (including connection), 15s by default
12. Filepath to store output to.
    - Note that this allows the response to not be stored in memory so you can download large files.
13. Additional HTTP headers to send in the request (in an array)

#GET Request Parameters
To make a GET request, you can use the `add_get` function. It's a bit simplified with the following parameters:
1. URL to be downloaded
2. Referer string
3. User Agent String
4. Filename to store your cookies.
    - Note that if this field is null, cookies will not be stored at all
5. Whether or not to clear cookies every time a request is made (true/false)
6. IP Address of HTTP Proxy (null by default)
7. Port of HTTP Proxy (null by default)
8. Connection timeout (in seconds), 3s by default
9. Completion timeout (in seconds) for the entire request to be done (including connection), 15s by default
10. Filepath to store output to
11. Additional HTTP headers to send in the request (in an array)

Note, you can call as many add's or add_get's as you want in a single request.

#Output
The output of the function is an array with the data and a range of parameters. Each request will be returned in the order they were added to the library.

Here's an example output from the example code above:

```
Array
(
    [0] => Array
        (
            [success] => 1
            [message] => Saved to File
            [file] => /home/david/Documents/Business/potentPages/http/out.html
            [header] => 
            [header_array] => 
            [body] => 
            [request] => Array
                (
                    [url] => https://potentpages.com/
                    [referer] => 
                    [user_agent] => 
                    [cookies_file] => cookies.txt
                    [proxy] => Array
                        (
                            [success] => 
                            [ip] => 
                            [port] => 
                        )
                )
        )

    [1] => Array
        (
            [success] => 1
            [message] => Success
            [file] => 
            [header] => HTTP/2 200 
server: nginx/1.15.6
date: Fri, 12 Jul 2019 16:46:29 GMT
content-type: text/plain
content-length: 15
last-modified: Fri, 21 Jun 2019 18:48:53 GMT
etag: "5d0d2695-f"
expires: Fri, 12 Jul 2019 16:46:28 GMT
cache-control: no-cache
set-cookie: request_uid=a8b96b48d417d4149ca2ae094e043753;Path=/;Max-Age=60
set-cookie: browser=a1a9a0b11acd64f1b061b68c6fb3834e;Path=/;Max-Age=31536000
x-location: /
accept-ranges: bytes
            [header_array] => Array
                (
                    [http_code] => HTTP/2 200 
                    [status_info] => Array
                        (
                            [0] => HTTP/2
                            [1] => 200
                            [2] => 
                        )

                    [server] => nginx/1.15.6
                    [date] => Fri, 12 Jul 2019 16:46:29 GMT
                    [content-type] => text/plain
                    [content-length] => 15
                    [last-modified] => Fri, 21 Jun 2019 18:48:53 GMT
                    [etag] => "5d0d2695-f"
                    [expires] => Fri, 12 Jul 2019 16:46:28 GMT
                    [cache-control] => no-cache
                    [set-cookie] => browser=a1a9a0b11acd64f1b061b68c6fb3834e;Path=/;Max-Age=31536000
                    [x-location] => /
                    [accept-ranges] => bytes
                )

            [body] => Testing 123!@#

            [request] => Array
                (
                    [url] => https://potentpages.com/test.txt
                    [referer] => 
                    [user_agent] => 
                    [cookies_file] => cookies.txt
                    [proxy] => Array
                        (
                            [success] => 
                            [ip] => 
                            [port] => 
                        )
                )
        )
)
```

##Header Parsing
If the document is stored in memory, the header will be parsed. The raw header is stored in the "header" index, and the parsed header is stored in the "header_array" index.

##Body
If the document is stored in memory, the body will be saved in the "body" index. If not, the result will be saved in the file that you specified.

##Request
Your original request is stored in the "request" index.


