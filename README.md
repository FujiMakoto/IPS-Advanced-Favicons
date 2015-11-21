# Advanced Favicons

## Introduction
Advanced Favicons is a third party IPS application that handles the tedious and deceptively complex task of generating favicons for your IPS community website.

**What do you mean "deceptively complex"?**

When many people think favicons, they think of a single favicon.ico file that is located in the root of your web directory. In truth, favicons today have morphed into a giant, unstandardized and convoluted mess that involves needing ***more than 25*** different variations of the same favicon image along with multiple schema documents for full compatability accross all browsers and devices.

You *really* don't want to have to create all of these images and files yourself, do you?

I hope not. Advanced Favicons to the rescue then!

This application will handle the mundane and complex task of generating all of these files for you in an easy, step by step process. All you need to do is upload a single, high quality logo image that you wish to use as your favicons base image, answer a few basic questions about how you'd like your site to be displayed on mobile devices, and you're done!

## Demonstration

You can see a quick "before" and after demonstration of the application here (you can replace community.invisionpower.com with your own forum as well),

**Before**: http://realfavicongenerator.net/favicon_checker?site=community.invisionpower.com

**After**: http://realfavicongenerator.net/favicon_checker?site=https%3A%2F%2Fwww.makoto.io

## Setup and configuration
First, install the application in your AdminCP by uploading the included .tar file in the Applications page, located under the System tab.

After the application has been installed, navigate to the Manage Favicons page, located under the Customization tab.

From this page, launch the Setup Wizard and follow the on-screen prompts. That's it! Once you've completed the setup, you'll be given a link which you can use to ensure the application is working correctly.

**Note:** Due to what appears to be a [bug](https://github.com/RealFaviconGenerator/realfavicongenerator/issues/202) with the RealFaviconGenerator website at the time of writing this, you may be told that your site is missing some Windows 8/10 images, when this should not be the case. You can load these images directly yourself to confirm nothing is wrong here.

## License

```
The MIT License (MIT)

Copyright (c) 2015 Makoto Fujimoto

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```
