fs     = require 'fs'
{exec} = require 'child_process'

pathFiles  = [
  'themes/Bluebird/scripts/tree'
]

task 'sbuild', 'Build all files', ->
  pathContents = new Array remaining = pathFiles.length
  for path, index in pathFiles then do (path, index) ->
    # console.log "#{path}"
    fs.readdir "#{path}", (err, files) ->
      fs.exists "#{path}/app.coffee", (exists) ->
        if exists
          fs.unlink "#{path}/app.coffee", (err) ->
            throw err if err
            console.log 'Removed Residual app.coffee file.'
      fs.existsSync "#{path}/app.coffee", (exists) ->
        if exists then console.log('still exists')            
      for file, index in files then do (files, index) ->
        if !/\.coffee$/i.test(file)
          files = files.splice(index,1)
      appContents = new Array aremaining = files.length
      for file, index in files then do (files, index) ->
        # console.log aremaining, appContents, files.length
        fs.readFile "#{path}/#{file}", 'utf8', (err, fileContents) ->
          throw err if err
          appContents[index] = fileContents
          process(path) if --aremaining <= 0
      process = () ->
        # console.log "#{path}"
        fs.writeFile "#{path}/app.coffee", appContents.join('\n\n'), 'utf8', (err) ->
          throw except if err
          exec "coffee --compile #{path}/app.coffee", (err, stdout, stderr) ->
            fs.unlink "#{path}/app.coffee", (err) ->
              throw err if err
            if err
              console.log "errored?"
            console.log stdout + stderr
            console.log 'Done.'