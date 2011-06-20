git-daily
===========================

"git-daily" is a tool which helps you to do daily workflow easier.

Sub-commands are::

    git daily init
    git daily config
    git daily push
    git daily pull
    git daily release open
    git daily release sync
    git daily release close
    git daily version
    git daily help


Installation
--------------------------

Install from openpear.org ::

    sudo pear channel-discover openpear.org
    sudo pear install openpear/Git_Daily

Install develop version ::

    cd /path/to/dir
    git clone git://github.com/sotarok/git-daily.git
    ./git-daily/src/bin/.gen-local-git-daily.sh
    sudo ln -s /path/to/dir/git-daily/src/bin/git-daily-local /usr/local/bin/git-daily
    sudo ln -s /path/to/dir/git-daily/src/Git /path/to/pear/Git

You can find the path ``/path/to/pear`` by the command  ``php -i | grep include_path`` .

Cheat Sheet
--------------------------

Initialization
^^^^^^^^^^^^^^^^^^^^^^^^^^

To initialize, use ::

    git daily init


Configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^

* To show configuration for git-daliy use ::

    git daily config

* To set the configuration use ::

    git daily config [<key>] [<value>]

Release
^^^^^^^^^^^^^^^^^^^^^^^^^^

* To open the release process of the day, use ::

    git daily release open

* To sync opened or closed daily release process, use ::

    git daily release sync

* To close daily release process, use ::

    git daily release close


License
--------------------------

::

     The BSD License
     
     Copyright (c) 2011, Sotaro Karasawa
     All rights reserved.
     
     Redistribution and use in source and binary forms, with or without
     modification, are permitted provided that the following conditions
     are met:
     
       - Redistributions of source code must retain the above copyright
         notice, this list of conditions and the following disclaimer. 
       - Redistributions in binary form must reproduce the above
         copyright notice, this list of conditions and the following
         disclaimer in the documentation and/or other materials provided
         with the distribution. 
       - Neither the name of the author nor the names of its contributors
         may be used to endorse or promote products derived from this
         software without specific prior written permission. 
     
     THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
     "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
     A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
     OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
     SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
     LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
     DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
     THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
     (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
     OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.



