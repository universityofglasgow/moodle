********************
*  Keyboard Input  *
********************

Keyboard Input is an HTML5 component providing a graphical interface that is
meant to be used in the context of interacting with a piano keyboard.

It takes as input an XML string describing the initial state of the keyboard,
along with a callback function that is called when the user changes the state
(e.g. by selecting/unselecting a note on the keyboard). On callback, the updated
keyboard state is passed back as an XML string.

A description of basic use is provided in the following documentation file:

[Keyboard Input component directory]/docs/modules/KeyboardInput.html.

*******************************************************************************

VERSION

1.1

*******************************************************************************

DEPENDENCIES

This component is dependent on the YUI Javascript Library
(http://yuilibrary.com/ - It was developed and tested with YUI version 3.13).

In particular, it requires access within its scope to a Y object with the
following modules loaded:

base
node
datatype

This repository does not currently contain the required YUI modules, and so
the component above will not run "out of the box".

One good way to use this component with YUI is to package it as a YUI module.
To see an example of how this can be done, refer to
https://github.com/brissone/moodle-qtype_musictheory, where the component is
packaged as a YUI module within Moodle.

*******************************************************************************

LICENSES

The Keyboard Input component was created by Eric Brisson.
The source code is available under the following license (MIT License):

/* Copyright (c) 2014 Eric Brisson

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
 THE SOFTWARE. */

------------------------------------------------------------------------------

The Keyboard Input component makes calls to the YUI Javascript library, which
can be used under the following license (http://yuilibrary.com/license/):

Software License Agreement (BSD License)
Copyright © 2013 Yahoo! Inc. All rights reserved.

Redistribution and use of this software in source and binary forms, with or 
without modification, are permitted provided that the following conditions are 
met:

Redistributions of source code must retain the above copyright notice, this list
of conditions and the following disclaimer.

Redistributions in binary form must reproduce the above copyright notice, this 
list of conditions and the following disclaimer in the documentation and/or 
other materials provided with the distribution.

Neither the name of Yahoo! Inc. nor the names of YUI's contributors may be used
to endorse or promote products derived from this software without specific prior
written permission of Yahoo! Inc.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON 
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
