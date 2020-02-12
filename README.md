# straceout-utils

[![Build Status](https://travis-ci.org/mpchadwick/straceout-utils.svg?branch=master)](https://travis-ci.org/mpchadwick/straceout-utils)

A set of tools for working with strace out.

## Installation

### PHAR

- Download the latest release from GitHub

### From Source

- Clone the repository from GitHub
- Run `composer install`

> TIP: If installing from source, execution entry point is `bin/straceout-utils`

## Features

### MySQL Query Results 

strace out will include the packets received from MySQL in [Client / Server Protocol](https://dev.mysql.com/doc/internals/en/client-server-protocol.html) format. This is not a format that can be read by humans. straceout-utils will process these packets and transform them into a format that is digestable for humans.

> **NOTE** You must use the strace `-x` flag so that the packets are in hex format

#### Example

```
$ traceout-utils process strace-out.txt
```