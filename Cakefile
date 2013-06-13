fs     = require 'fs'
{exec} = require 'child_process'
fspath = require 'path'

pathFiles  = [
  {path: 'themes/Bluebird/scripts/tree', outputName: "JSTree"}
  {path: 'themes/Bluebird/scripts/jquery.taggingAutocomplete', outputName: "jquery.taggingAutocomplete"}
  {path: 'themes/Bluebird/scripts/bbUtils', outputName: "bbUtils"}
]

task 'sbuild', 'Build all files', ->
  pathContents = new Array remaining = Object.keys(pathFiles).length
  for obj, index in pathFiles then do (obj, index) ->
    outputName = obj.outputName
    tempName = "#{outputName}_temp"
    path  = obj.path
    filesToRead = []
    fs.readdir "#{path}", (err, files) ->
      fs.exists "#{path}/#{tempName}.coffee", (exists) ->
        if exists
          fs.unlink "#{path}/#{tempName}.coffee", (err) ->
            throw err if err
            console.log 'Removed Residual app.coffee file.'
      fs.existsSync "#{path}/#{tempName}.coffee", (exists) ->
        if exists then console.log('still exists')            
      for file, index in files then do (files, index) ->
        if fspath.extname(file) is ".coffee"
          filesToRead.push(file)
      appContents = new Array aremaining = filesToRead.length
      for file, index in filesToRead then do (files, index) ->
        fs.readFile "#{path}/#{file}", 'utf8', (err, fileContents) ->
          throw err if err
          appContents[index] = fileContents
          process(path) if --aremaining <= 0
      process = () ->
        fs.writeFile "#{path}/#{tempName}.coffee", appContents.join('\n\n'), 'utf8', (err) ->
          throw except if err
          exec "coffee --compile #{path}/#{tempName}.coffee", (err, stdout, stderr) ->
            fs.unlink "#{path}/#{tempName}.coffee", (err) ->
              throw err if err
            if err
              console.log "errored?"
            fs.rename "#{path}/#{tempName}.js", "#{path}/#{outputName}.js" 
            # console.log stdout + stderr
            console.log "wrote #{path}/#{outputName}.js"
            time = new Date()
            currentTime = "#{time.getHours()}:#{time.getMinutes()}:#{time.getSeconds()}"
            console.log(currentTime)
