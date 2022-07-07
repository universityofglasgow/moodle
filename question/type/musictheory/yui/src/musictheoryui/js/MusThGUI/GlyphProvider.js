/* Copyright (c) 2013 Eric Brisson

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

/**
 * The GlyphProvider object takes care of loading and returning
 * image objects to be drawn on the canvas.
 *
 * @class GlyphProvider
 * @namespace Render
 *
 * @constructor
 * @param {Function} imagesLoadedCallback Function to callback once all images
 * have been loaded.
 * @return {undefined}
 */
MusThGUI.Render.GlyphProvider = function(imagesLoadedCallback) {

  this.imagesLoadedCallback = imagesLoadedCallback;
  this.numImagesLoaded = 0;
  var parent = this;

  this.wholeNote = new Image();
  this.wholeNote.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABEAAAAKCAQAAAD4dX0pAAAAv0lEQVQY01XNPUpDQRQG0PMe' +
      'koASxUbEJiCCWLgBm5QWLsNScBGCYpc6G5C0QXegnUrAxs7Cn8agjbFTb4p383jeab755sxM' +
      'oZoNew6s2rKg682NkVuveap04VMYaIGedyFMDCqwYiyEP+35Hdsi14cWJ7m505zDGg35yvjw' +
      'j7S9zFFZl7s6DfJjnOm31K/r4wYJ00xXLLvPJyd2si7wJIRHS9W/Z4mmeon6QjjVqXwhrDmy' +
      'r2vdtWebFl069w0ziIFGBFp4rmoAAAAASUVORK5CYII=';
  this.wholeNote.onload = function() {
    parent.imgLoaded();
  };

  this.wholeNoteColor = new Image();
  this.wholeNoteColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABEAAAAKCAYAAABSfLWiAAABHElEQVQoz3XSv0tWURgH8M+9' +
      'RFAgUaciW8IQokFoinCJOyn0BzjI3QSHUAz/B3UI3B07bkIQuZ9AN0FXXUM3b0PooJRvy3nh' +
      'dvD9jt9fz+E5T6WHpu2eYxof8BCTuIMXOMM+vuEgxXA6zFU5XGMbszm8haUUw3XW32MHT3Lu' +
      'HF9TDItQNW33AD/wJhsGuJdiuCpe+QrH/scvjNdY7RXAYVkAKYYTLBT0I3yp8akQ7hqNbZwW' +
      '3Fx9i3GqabuxESV/cFRwf2ts3mJeHlEywEXB7db4jMNCWGna7nWx2CrFcIN3PfoY83WK4Xe+' +
      'jfWe+BgH+WuHix00bbeJiUyt4W2K4bLqTRk0bfcUHzGTD+wZ9vATL3Ef37GRYrgcDvgHQYtR' +
      '/HvclQsAAAAASUVORK5CYII=';
  this.wholeNoteColor.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemUp = new Image();
  this.qNoteStemUp.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAQAAAD1XwhOAAAAgklEQVQoz+3OoQ0CQRAF0McG' +
      'dwJBgkEQFF0gKAB1kgJQNIWCDlCECjgJCoNBEAwGzCIIlyO7JdwfM3n5mQxpBk4ZNRSDbFpu' +
      '+S/deutZmXrZ2P+oby3WcxWhcG/gdxTOCb6ZJxjdgmXmkR1V7kRwTLozGHs0mhcT6GBkoRQc' +
      'bFWe8AGFfUDrkMtAfAAAAABJRU5ErkJggg==';
  this.qNoteStemUp.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemUpColor = new Image();
  this.qNoteStemUpColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAYAAABfVsDFAAAAtElEQVQ4y+3UMU7DQBAF0OcV' +
      'qVIgYSkFFOkCp0AyuUJKTE3FIThB7uAKbkC1AlEDR0BIKa2koEFu0tiIwsAeYKd+M9r5I20h' +
      'oaq6neEpSKsJTlMxyDjjjDPGwcjvc4gbnOMLd4hQ/EAl1rgcGbrBSdHDKT5w9Oebe/jyH0QX' +
      'sMQiYb9twHViGA8Bxwmwi015FfCagC+Go9xi9wt6x1lsyufvnKu6nff5rvoBj7jHW2zKz6Fz' +
      'DxqCIyfsYg9VAAAAAElFTkSuQmCC';
  this.qNoteStemUpColor.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemUpToolbar = new Image();
  this.qNoteStemUpToolbar.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAbCAQAAAAEhm0LAAAAcklEQVQoz+3IsRHBcBjG4UeS' +
      '1pnApTGLAVhFZ4uUBjBBdApqhQFUHKXeXbhPQZF/cmcCv6/53oe0zKUjcpHp9acf1C+K7zNW' +
      'Gnq4fWZhZu3q6a4WDCy8ROtMkxmCbQc2nBI4m7Bswd6c3FFjpHGwUtnxBmZsNrKFQDp/AAAA' +
      'AElFTkSuQmCC';
  this.qNoteStemUpToolbar.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemDown = new Image();
  this.qNoteStemDown.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAQAAAD1XwhOAAAAgUlEQVQoz+3OsQkCQRAF0HcX' +
      'CIIaiQ2YWYFgFRZwFRymNmNmFZqYWIJgBWtmYOIlgmewIKe7Hej/2WOYmULM0Fxl5mFv4xJx' +
      'IWjfvZvAskOxR6aahNtSre87V27prJUMnsnxGJqPNVujuD7oqR3srLvHg0HyiVI2f/5dLvJ8' +
      '8kz5Beo8SqCGCGcrAAAAAElFTkSuQmCC';
  this.qNoteStemDown.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemDownColor = new Image();
  this.qNoteStemDownColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAYAAABfVsDFAAAAtUlEQVQ4y+3RMWoCURSG0fMG' +
      'm0DEYkqraJcVCEJgliCppwmkyi7cgqXlq9LY2ziNO7DIBrRzAjYKBkKaBCRB89KG+erD5Ycb' +
      'nFSUdRsDlLjFG+aYVjHfhBM4xDO6frbHTfiEI8xcbhmKsu5jhatfsAxPKRDbDA/SGmfoJMCX' +
      'KuaTLPHq3dfmA3ZnUESnivkWWnhFD4+4xxELTKqYH3z72roo6+uULambG9zgBv97HP6CV3hP' +
      'wR/6KieA6EyktQAAAABJRU5ErkJggg==';
  this.qNoteStemDownColor.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemDownToolbar = new Image();
  this.qNoteStemDownToolbar.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAcCAQAAAAZg12zAAAAXklEQVQoz2NggIAchoMM9xiO' +
      'MRRBuOoMvxj+w2E5A4MUEhcCGdahCfxiZPjNwMKADOIZ0NScYWBgYPjO8A/K/cswBaLwLAMD' +
      'Qy3DdIYIhN6zDGiAiYFhVIgGQs/QhQAJby5DVGAeeQAAAABJRU5ErkJggg==';
  this.qNoteStemDownToolbar.onload = function() {
    parent.imgLoaded();
  };

  this.tClef = new Image();
  this.tClef.src = 'data:image/png;base64, ' +
      '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';
  this.tClef.onload = function() {
    parent.imgLoaded();
  };

  this.bClef = new Image();
  this.bClef.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABsAAAAgCAQAAABJYMuwAAAB6ElEQVQ4y5XUT0hUURTH8c+M' +
      'jpn9FQwjLReSCAlFUBlJEEHUIkhwEVKQixZCi9omLYqwXavauIho0yKUKAzaBbUzQaIsWxRk' +
      '/lklRtZU6m3h6+mMM/r8PXicd8/93nvuPeedlEVtVq1FoyYZ7/3yzCdTZhRQKnoHF11R7pEh' +
      '49apc0KbrAG97ppVRL2Cc0qjZRaWOioI5n21txCS0Sc4VsBzWFYQ/HUmHitTCWndggtFojgv' +
      'CIJZB6IYnhrWxjYTJt3ywwdd1udhVYYi8Bs4JAhGaYmGF57nNuaBD2LfdaQMmtRJdw4W3MjD' +
      '2mPPCxXYYidpZctOk6vB2Kq3HdNGSevPm1aR9/0ztrba9N9Me2c8Z9r9ggUBQVjq6PQnjn98' +
      'IStLtCv2TWnKdZ3yxJjP7mhYlrn6GBtRk+8skVEqXSDhp2PspQ1SzupRXho558wVqZP9sTVs' +
      'RrWH2JO2slIOxnYf5iHvEguoxkgU4mQ00qjDqmqNT9ZuDXodQR+X5G9VLd7i8eRQlbEIeiyT' +
      'HLsXQVklyaHWGGpIDjVHXeR7wR5TRPv8jvY6khw6aSLqGM3JoZvRTq/UJr/y/qildiVFKl0y' +
      'JZjWY3fOX72COnwRBLftSJalWlfNyBpyLXndpVxWZ8Bbb9ZS4f8AkCq9FpfsQ28AAAAASUVO' +
      'RK5CYII=';
  this.bClef.onload = function() {
    parent.imgLoaded();
  };

  this.cClef = new Image();
  this.cClef.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABgAAAAmCAQAAAB0DpOuAAACIUlEQVQ4y5XVS4iOURgH8N/H' +
      'NzOIJCXl0kwuoYhpopmFGBs2g71y2UlZIMYl5ZoGSzYWUjakkA0l1+QuFE3kmiRyK5fBfF6L' +
      '78zL9zlHed7Fc/7/9/2/T+e5nFOQKdtaO5QstyfgPsZrNUJJp9Oe+Vmme4nbZBct9cghx9W5' +
      '4YIxPa+y8LSjZBkYJ5Pp0hS+adXtm8Z0hL2gTkfAZzxQ66YJKcHz4D/lzAGwX01csNptvLUz' +
      'Zy6DJqOLUcFr0/T1w9ecKYUUzS0msvTd9wp8KfiJqbSmbFxKUFCI8ldSgpUaKvCw4J+kBFuN' +
      'qMAjgz8RF4xVZ3YFMxA88CIu2IV2vf9gWsEan2OCejNCQ/QJTI0lWO9YvDUWGACmu6cNE5wz' +
      '2Hzbobpw3zTaEtZzvbPONm+dN0tXvL13exPwkfC+troifwq6c/TesHjpUnXIFPLh/YegYJ8P' +
      'YFC+k5rKSNURrpoTVou0anbYDWe15wmOzvSGnHsU0npLZl5aUO+jTOZ8/t+iNzIrUpt+6nZo' +
      'h57cdzuK3eb9LShveRU2h7Es20mwUb9qwUtw3SenIvwUw1N12OVVJDJtqUNgcxXuDL6hmKx0' +
      '3Kb+76lxPxWhVl8/fMlxS/DX4hGGuOyDJ6bmTDGcf2fjgg6NGGJDzjSDux7HBT2HSv+cWRiG' +
      't6u6l2YmL5SsjOKb7jTZQXcN1aJokk2uWOxhWsAdLUaZoV5Jp6bfl+Iv+S6xhZ7Q1G4AAAAA' +
      'SUVORK5CYII=';
  this.cClef.onload = function() {
    parent.imgLoaded();
  };

  this.sharp = new Image();
  this.sharp.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAcCAQAAAAZg12zAAAAxElEQVQoz6XQMUpDQRCA4W8T' +
      'BUEwjZWxEAyrRDyFjdhJLmDrKeIFPIu3iFpbCAqmiIKQREHB1xjWYo0vT1IomWlmfgbmnwGW' +
      'waELqIEbu1jVLNGOFZBKVIn/o4mExtJ3+yo6sufBkJZjqZI+foGhn7IwVjizQfLo06noxFXe' +
      'GGyq67nzLsxKhD+rpnz2VHVN1LZlZMy2ruRZMdUJeXgmBirmLzqaPLk1cS46cJkH15Hso+M6' +
      'S4xAfZFHz0Fv+mVzLyLkJV//bUd1gpAvqQAAAABJRU5ErkJggg==';
  this.sharp.onload = function() {
    parent.imgLoaded();
  };

  this.sharpColor = new Image();
  this.sharpColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAcCAYAAACzipU4AAABLElEQVQ4y8XSv0ocURQG8N+u' +
      'I1EirDBFBBUMSv4UsnVsZDofQATB6S208wEUfABbu8B0gZAXME7nA2gngoIowjoKK4iNbJo7' +
      'OCy7SwrF09zvu+fjnnO+ewiRpMVwBS8lafGn5HUvcZKkxbeAP2Kyl+grRiq800vUN95e9Fz2' +
      'lKRFo1YZu4MZfMAWVtFCK0rSYg7zQXvR9eIYPkc4xuiAlm7rPQRPuAvnDppRSFxhApv4iwWs' +
      '51m8DfU8i2t5Fk9hCEd5Fp/iAbV+FtRe3cxO9YO7zVzEDVawgQhFlKTFLNaC9hcaXSsTRzir' +
      'XHzqUfpyUOP3WMaPCNdo4wv2sI9p7OZZ/Lucrpln8feAfwYzx6uT1/Msvq2UGHqf9f0vURvn' +
      'JYkqiTM8BnyAwzLxDz5LRoo+XtMwAAAAAElFTkSuQmCC';
  this.sharpColor.onload = function() {
    parent.imgLoaded();
  };

  this.sharpFigBass = new Image();
  this.sharpFigBass.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAcAAAAPCAMAAAAf3yWMAAAA/1BMVEVOTk4AAAAiIiLw8PDP' +
      'z8+Hh4fMzMwjIyNTU1MJCQlhYWGcnJwCAgIUFBQTExMWFhYcHBz4+Pjv7++EhIQkJCT6+vrX' +
      '19f39/d/f3/R0dH8/PzW1tb29vY2NjbNzc2GhoZQUFBGRkagoKCdnZ1UVFSoqKirq6uVlZVK' +
      'Skqvr696enqWlpZkZGSOjo6NjY3+/v5dXV1JSUlISEgqKirm5ubU1NRFRUX09PTd3d1eXl59' +
      'fX0SEhLg4OD19fViYmK5ublEREQyMjKxsbGJiYkeHh6AgIAEBAQ6OjodHR22trb9/f3p6em+' +
      'vr6IiIiKiopBQUFzc3PQ0NBSUlLc3NzGxsappTWHAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgF' +
      'HUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdChgTJA0sUEKyAAAAg0lEQVQIHQF4' +
      'AIf/AAAHAAAHAAAAAAcAAAcAAAAABwAABwAAAAAHAAAzBwcABwEBAQcHBwAHB0ZBBwAAAAAH' +
      'AAAHAAAAAAcAAAcAAAAABwAABwAAAAAHUDAOAQcABw8BAQcBBwAHB0c6BwAAAAAHAAAHAAAA' +
      'AAcAAAcAAAAABwAABwAAojUC3ILbwfkAAAAASUVORK5CYII=';
  this.sharpFigBass.onload = function() {
    parent.imgLoaded();
  };


  this.flat = new Image();
  this.flat.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAYCAQAAABt0HSbAAAAkUlEQVQY083OsQpBARhA4e/e' +
      'JK9gttoUs8kjGIwWm8HDeANZlAxKmU3KbLAahYi68RukLk/gbOdMh6WKLy4aeU09xHf44Z/C' +
      'U8dWOOgrcLMVNjIhDAgrZUU9mbDnqgmabsI9lbmAqiIWqUSCkpYUU07qaLgLM8kn7IT5++So' +
      'ZiSMkcDZRBgqfF5D6Obn19p5fQFtRC8aqop0JQAAAABJRU5ErkJggg==';
  this.flat.onload = function() {
    parent.imgLoaded();
  };

  this.flatColor = new Image();
  this.flatColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAYCAYAAADH2bwQAAAA6ElEQVQoz+3RoUpEURDG8d9e' +
      'LrrFoNdkVDAJRq0HBMFgMCl6mtVkUxB8A4tgEYSLCzbBIIJwn0Cw+gSmg0Usuq7Bs7As6z6B' +
      'HwwzMH+Y+WaEmB5DTAv+UIFVzI4DuuiNA8bqH/hVmfN3iGkXJ1jEG05xXmIC17nxjC9M4wxl' +
      'gTYS5rCCgwzBYYkPHDd19QohppcMlJgp8In3gb2W8lh4KNDKIcTUxvqAu9thm8tYy/Udrsoh' +
      'oINJ3Dd1tdk/VA/dEFMH87hp6mojxNTqAwWOsIML7EFTV73+Jaewhf2mri5H/eIJ26Oa8AMr' +
      'MTaLY1mrzwAAAABJRU5ErkJggg==';
  this.flatColor.onload = function() {
    parent.imgLoaded();
  };

  this.flatFigBass = new Image();
  this.flatFigBass.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAPCAMAAADwHU6yAAAAolBMVEWnp6ckJCQAAAAvLy/q' +
      '6upoaGgnJydwcHAqKiodHR1ERESdnZ22trakpKRMTExPT0/19fXv7+/29vaurq61tbXY2NgM' +
      'DAzMzMxUVFSNjY0iIiKsrKwCAgIBAQEpKSn9/f2lpaUyMjLPz88FBQWWlpbS0tLDw8PQ0NA+' +
      'Pj4mJiYSEhKHh4cXFxeoqKg1NTUTExMREREZGRnf39+cnJwDAwOenp4V5r8VAAAAAXRSTlMA' +
      'QObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdChgTKjDq' +
      'uyMtAAAAMUlEQVQI12NgZAADvBQjlAIzmA3BtCaQyaQIVsLECKIYmUAadHTAKkEkAxtEnxyY' +
      'BAAquAFD8JXS1AAAAABJRU5ErkJggg==';
  this.flatFigBass.onload = function() {
    parent.imgLoaded();
  };

  this.natural = new Image();
  this.natural.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAeCAQAAAC7iZeGAAAAv0lEQVQY05XQMUtCcRQF8N97' +
      '/InEskEIHJpyDQdBEPEjiBAEra429F36IO1tDuVUWyDRUJD5CQJ5kr2Gl+iLQt650znce+65' +
      'lwuXcngw2qSxxCIv/MJ2IeTYnjhggRNNDceiINV1r+ZAORtJVTT/M12uTadePKvqc+fJqZZD' +
      'DKRB5NX1T99O5hEViJ5kW5bgSNtZFqzuSs++kl24lW7WH+evss48KusEE7EbY+/enOsEQ7HE' +
      'J/gimBd9clHhw/wbjBksgn0qOpgAAAAASUVORK5CYII=';
  this.natural.onload = function() {
    parent.imgLoaded();
  };

  this.naturalColor = new Image();
  this.naturalColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAeCAYAAAARgF8NAAAA8UlEQVQoz7XSvUrEUBCG4Sch' +
      'iOJfkcrCSluxsBKxyAWIiAo2abRTxHvxFtILllbprCwFQQsFf7p0wrKL69okcFzcgMoOnOac' +
      '98x8883I8uoky6szIyLGIXbagC56bUBr/B9IRj1keTWDuAF69eUK1rCKJUQJBtjM8uoGC5jH' +
      'dFhigLn6569E9n8S+YJHPCDFdowI99itz15ZpEe4bDJEeCqL9GIo20SoIRqL1d2wi35g8SLW' +
      'sR8atZzl1Tm2MIspTH4DcPrncQ+7+YbbemAbCe7qTFe4xiuecdAAx83ylkX6EXTzCUlZpJ3x' +
      'Lu14gXd0vgC9Azb8J/WQGQAAAABJRU5ErkJggg==';
  this.naturalColor.onload = function() {
    parent.imgLoaded();
  };

  this.naturalPar = new Image();
  this.naturalPar.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABMAAAAeCAYAAADOziUSAAAABmJLR0QA/wD/AP+gvaeTAAAA' +
      'CXBIWXMAAFxGAABcRgEUlENBAAAAB3RJTUUH3wcaDykVgP9TjAAAAm5JREFUSMel1s2LV2UU' +
      'B/DPXO+MSpo6qTmMyNjAaJhoC3VhLgohwiwK3IW4kVy5dq3gRlf+AZW0cVNgviwUF0qLcKFI' +
      'o0KJvViKRYQvqY05Pxd9L1wuvzvzqzlweZ5zn3PPc873+Z7zXLrLOtzASv9Bipb3I1iG5TNx' +
      'thHv4ykm87TJx1jV5mw2DuN1dHoIZAs+bVvcmUiG8S7uY/MUzl7LprubC4txDucT4fYenMF3' +
      'GMeceppr8RZO4O+acTPdBcFpQfRjWI0PK2f9+CCLlxofzw+Gu3AIn+AMdmT9JGbhbfSXGMBH' +
      'uI3fGo6OJO2Xq1Qiy1HiWvRNGC3xKhbhKv5spDfWgtUk+jCBbwPTSIE3YnA7oE8lT7s4vZr5' +
      'aIn1Uf7AXw3jCfye9H/Iye0MTlUGt+rOXolyv8b4vhgezglfxz0sxLYaCzo1nFeUGIzypBHV' +
      'AxzHxdq7uV1K8FHGl4rGLnXpC22mk38ylgWeRZnl/8lAdThFAK4KfTqZ6NJJ5mW8W6YJvpkS' +
      'KWthd/Aw8zVplBsyPqtBsTTzn8taCS3GCzk1eBEHs/NwiD1Y41dVjiOZ3yhxIcpwHNyrHcY7' +
      '2b1N+hJ1BzcL/IRfMZrdm8Zt0gnOY7iCH4uA+jmWYKhLB66IOY6vw78iqW6IzTe4WabevsK+' +
      '3AFnE+nl4Dme7jAegp4JjSbxXvrf6TpP58fhpbC8TJsZaKQ2FJv90e8kqqKezgN8lr6+IvT4' +
      'JRC0dY+tuQ4PVKdbx+bL3AN7eiDvY+zFqTz/1lPDaG+Pt/gcfJFGoM3Z93mmk9k42uvvwVQk' +
      'rb7pn6mzh+msj7r9OjwHDDOOLnjafMoAAAAASUVORK5CYII=';
    this.naturalPar.onload = function() {
    parent.imgLoaded();
  };

  this.naturalParColor = new Image();
  this.naturalParColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABMAAAAeCAYAAADOziUSAAAABmJLR0QA/wD/AP+gvaeTAAAA' +
      'CXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wcaEBAyPAuPEAAAAB1pVFh0Q29tbWVudAAA' +
      'AAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAC1UlEQVRIx5XVS4gdVRAG4O82d5wYdSLpiaL4' +
      'wCxCDCiIoqKodMxGESQBV9Ki4saFiru4EFyIoiBkqQtRaEQxBAwIJtm0QQQRGR/4gIiCSALR' +
      'nFmMMeqM5rpItZ7puddcC5o+dR7/qVP1V9VAT6o6wb14Dbe1TfmVKaXogWibEi7DHNafDaA7' +
      'B8Mxi1diBaP4JgFcghNtU65UddI25RnLOqWq0zM4jF+meNFbeDx7zRmwANqKR/HhOIt7MsIn' +
      '2F3Vadsan+FBXITXMftfSG1TjrAP89he1WlQ1emMBVWdtuC+uPEwHo5zf2V+KnBFBOczHMWP' +
      'eBZ726Y8XlR1GuAObMbbbVMuZ0+5uqpTXdXpCbyBvTiIXQH2PjZgZxfNWdwVAIciUiPMYA/O' +
      'xTnxdXJV25R/VnX6IvSH8PIQ63B3TH7cc8+mCW47Hf8fcAo3VnW6sMAtYd1RLE0RxVyO4KcY' +
      '3z/EjlAWg6x9OYmEr8OKndnaiViHHUNcE8rP6Jw/iP9zEd1FfI6bMv8K8O7MtUOUYzjX0WJf' +
      '25QLGT1ms4u6Szt9rsB1Y542CP8Uppf5YoKfJlWGicmP0wW+PRtYl8jBy0nWLg3xPbZl3Mmt' +
      'WKzqdHE4/eYIwLrens7SY0N8insiEDPZphl8EMw/L0AGvQs3RIbAQoFDoVya3TrK5ubjwGDM' +
      '0zbh/Bi/VwR/lmNhzv+Ty7Exxge6aO6PiesnlPMVLEThzGVzuOCjtikXC/yRPXVXRO4bfBdl' +
      '5xHcHh3rRfwWdBnihjj36j9pE8VxP7bigkiT9WHRclRWVZ224108HxX5ywDf0jbl0jB4dKSq' +
      '0zvYjVvbpjyYJXA/MzrZGD5+smtARcbuPVGGn5qy3z4Wlh3oLC86drdNeRwv4PcpI3kSL7VN' +
      'eWxVd8qsewUPTAE0Cpe82e/qa9r8msV/5++s6nSqqtPT4/YWY5J51bgnCb9mBXHV3r8BTYTp' +
      'IcgiIbUAAAAASUVORK5CYII=';
  this.naturalParColor.onload = function() {
    parent.imgLoaded();
  };

  this.naturalFigBass = new Image();
  this.naturalFigBass.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAPCAMAAADwHU6yAAAAilBMVEWVlZUSEhIAAACsrKw4' +
      'ODizs7OWlpavr6+tra329vb7+/siIiJHR0cpKSkCAgKXl5fS0tKysrLa2to2NjYuLi74+PgL' +
      'CwtQUFA+Pj7k5OQwMDCRkZEhISFiYmKgoKC8vLze3t6Dg4PJycmjo6MGBgZfX18zMzOUlJQm' +
      'Jia9vb1gYGD+/v5ubm4EBAQYyJtoAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ' +
      'cwAACxMAAAsTAQCanBgAAAAHdElNRQfdChgTGQlB1c4VAAAALUlEQVQI12NgZAADDIqJkZuB' +
      'kYkJQqkogiiQBAYlzcCNrJIJJMgAkQMBbhgPADXrASb/uuETAAAAAElFTkSuQmCC';
  this.naturalFigBass.onload = function() {
    parent.imgLoaded();
  };

  this.doubleSharp = new Image();
  this.doubleSharp.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAQAAAAnOwc2AAAAdUlEQVQI11XOoQ3CYBiE4ae0' +
      'Bo0sSccoYQoMIzTFoNiEMaowKCZgDBKCQKCKa/Ihfpq0J+7yirscIYQDqBMtTBUpCgNyS1Bh' +
      'oLATVs6eep3GSzYW90JoE4ybX7w9EuRqpa2LjZurD9b+l07gOL/Ug/t8M5u4Hy58IZKVjiCm' +
      'AAAAAElFTkSuQmCC';
  this.doubleSharp.onload = function() {
    parent.imgLoaded();
  };

  this.doubleSharpColor = new Image();
  this.doubleSharpColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAn0lEQVQY03XQIQ7CUBAE0Peh' +
      'Bl08xwDbG2CQlSSgMJwEBboOywFq6TFICIKkrjgSML/hh6RrZpKZ3Z3dUJTtx6+2dZUfoSjb' +
      'Oa69MDJc6QAZ3pGPMUm0WcR3b1zG7hyHomzv6HDGGg+EkI4vynYVDbCpq/w0lPEV8YlbKoR4' +
      '3SdmOmOBKS7YoUHI0hdgX1d5E2PscBha3SW8SYV/YxjgvqQXJ1gHIRdPAAAAAElFTkSuQmCC';
  this.doubleSharpColor.onload = function() {
    parent.imgLoaded();
  };

  this.doubleFlat = new Image();
  this.doubleFlat.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAA4AAAAXCAQAAACRmLYJAAAA/klEQVQoz83SvyvEcRgH8Nf9' +
      '8CM5m0vpNt0gZ7Io/4AYmA0Gf4JRSTGKusmg3HDKbLzIopRkc4NNSnR+lStX+H4Mvs7dcrP3' +
      '9H6ed8/T+/lBxZBfbFrWgrQJGfdxNCjbLkZCMwotHEkd8M/Fvk6jFHy1L4GGnHEZt3pEmDLs' +
      '04UbaorqguBDUHEmCIJneYK6bUtqcfLEon2R4IiGabAqCE7jA7wLoqS6a5AHVTAqhcskEug1' +
      '2+J+UheK6dj1nH6RFwl0W0DVYTqu3JCyJSuFFWPezHvlUc6BYAclewqCBwM/DZ+UBWug5Nid' +
      'cyOtrzEf893mMDGuzDT5urLeP+kbi0lPkzNfV2AAAAAASUVORK5CYII=';
  this.doubleFlat.onload = function() {
    parent.imgLoaded();
  };

  this.doubleFlatColor = new Image();
  this.doubleFlatColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAA4AAAAXCAYAAAA7kX6CAAABdklEQVQ4y+3TPWhUURAF4G/X' +
      'VYM/hT4VIWojWIixskgwzQvapdBASCEPRCzsUwpCQLARCwtBLC/RdIKFohavslIsU6QTC0V9' +
      'RNQiwurazMJl2U2xtVMNM5xzz8ycq6yaV2XVHDUQZdXcLatm2Yjo4Bz24/NA7zCObAf8i96Q' +
      'Xm9EHbSNGf+BYwL3jHuOKfzZzgCwVVbNcZwNF33E7jCHsmpmMYku3tWp+NAqq+YrnuAa9gZR' +
      'N0hfB9F09tgmpts4FKBHuI5vmZKL2MJVPI6ZD+BBB7+xWKfiRciaxEoA39SpKKP+HAuYwFwb' +
      'v7CRSTmV5etZfho7In/f32orWCcwP2LrM9gZ+f3OwJYvYV9sczMj3IUrmYpnnfxF3A459+IT' +
      '96XdxBn8xOU6Fd/b2R3XcBIP61Qsx/F7ZdVM4Ra+4Fidio3+DC3cwRJW6lTcyKSfwEu8xfk6' +
      'FT9y5xwM/Qt1Kp5moC7m4iSzw7y6jvkBEHzCKi4M8+o/M7lkrKLCiyoAAAAASUVORK5CYII=';
  this.doubleFlatColor.onload = function() {
    parent.imgLoaded();
  };

};

/**
 * Callback function called whenever an image has finished loading. Once
 * all images have loaded, call the controller's callback function so that
 * canvas initialization can complete.
 *
 * @method imgLoaded
 * @return {undefined}
 */
MusThGUI.Render.GlyphProvider.prototype.imgLoaded = function() {

  this.numImagesLoaded += 1;
  if (this.numImagesLoaded === 26) {
    this.imagesLoadedCallback();
  }

};

/**
 * Returns the specified accidental image.
 *
 * @method getAccidental
 * @param {String} accType The type of accidental image to return ('n', '#',
 * 'b', 'x', 'bb').
 * @param {Boolean} inColor If true, returns a color version of the
 * accidental.
 * @param {Boolean} figuredBass If true, returns a version to display within the
 * figured bass (only available for sharps, flat and naturals).
 * @param {Boolean} parenthesized If true, returns a parenthesized version of
 * the accidental
 * @return {Image} Returns the accidental image.
 */
MusThGUI.Render.GlyphProvider.prototype.getAccidental = function(accType,
    inColor, figuredBass, parenthesized) {

  switch (accType) {
    case 'n':
      if (figuredBass) {
        return this.naturalFigBass;
      }
      else if (parenthesized) {
        return (inColor) ? this.naturalParColor : this.naturalPar;
      }
      else {
        return (inColor) ? this.naturalColor : this.natural;
      }
      break;
    case '#':
      if (figuredBass) {
        return this.sharpFigBass;
      }
      else {
        return (inColor) ? this.sharpColor : this.sharp;
      }
      break;
    case 'b':
      if (figuredBass) {
        return this.flatFigBass;
      }
      else {
        return (inColor) ? this.flatColor : this.flat;
      }
      break;
    case 'x':
      return (inColor) ? this.doubleSharpColor : this.doubleSharp;
    case 'bb':
      return (inColor) ? this.doubleFlatColor : this.doubleFlat;
  }

};

/**
 * Returns the specified accidental image.
 *
 * @method getNoteValue
 * @param {String} noteValueType The type of accidental image to return
 * ('whole', 'quarter_stem_up', 'quarter_stem_down').
 * @param {Boolean} inColor If true, returns a color version of the
 * accidental.
 * @param {Boolean} forToolbar If true, returns a smaller version for toolbar
 * display (only available for quarter notes).
 * @return {Image} Returns the note value image.
 */
MusThGUI.Render.GlyphProvider.prototype.getNoteValue = function(noteValueType,
    inColor, forToolbar) {

  switch (noteValueType) {
    case 'whole':
      return (inColor) ? this.wholeNoteColor : this.wholeNote;
    case 'quarter_stem_up':
      if (forToolbar) {
        return this.qNoteStemUpToolbar;
      }
      else {
        return (inColor) ? this.qNoteStemUpColor : this.qNoteStemUp;
      }
      break;
    case 'quarter_stem_down':
      if (forToolbar) {
        return this.qNoteStemDownToolbar;
      }
      else {
        return (inColor) ? this.qNoteStemDownColor : this.qNoteStemDown;
      }
  }

};

/**
 * Returns the specified clef image.
 *
 * @method getClef
 * @param {String} clefType The type of clef ('treble', 'bass', 'alto',
 * 'tenor').
 * @return {Image} Returns the clef image.
 */
MusThGUI.Render.GlyphProvider.prototype.getClef = function(clefType) {

  switch (clefType) {
    case 'treble':
      return this.tClef;
    case 'bass':
      return this.bClef;
    case 'alto':
    case 'tenor':
      return this.cClef;
  }

};