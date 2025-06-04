import {useDropZone, useFileDialog} from '@vueuse/core'
import {computed} from 'vue'

/**
 * File selection composable
 */
export function useFileSelection(options) {

  //Extract options
  const {
    dropzone,
    allowMultiple,
    allowedFileTypes,
    onFiles,
  } = options

  //Data types computed ref
  const dataTypes = computed(() => {
    if (allowedFileTypes.value) {
      if (!Array.isArray(allowedFileTypes.value)) {
        return [allowedFileTypes.value]
      }
      return allowedFileTypes.value
    }
    return null
  })

  //Accept string computed ref
  const accept = computed(() => {
    if (Array.isArray(dataTypes.value)) {
      return dataTypes.value.join(',')
    }
    return '*'
  })

  //Handling of files drop
  const onDrop = files => {
    if (!files || files.length === 0) {
      return
    }
    if (files instanceof FileList) {
      files = Array.from(files)
    }
    if (files.length > 1 && !allowMultiple.value) {
      files = [files[0]]
    }
    onFiles(files)
  }
  
  //Setup dropzone and file dialog composables
  const {isOverDropZone} = useDropZone(dropzone, {dataTypes, onDrop})
  const {onChange, open} = useFileDialog({
    accept: accept.value,
    multiple: allowMultiple.value,
  })

  //Use onChange handler 
  onChange(fileList => onDrop(fileList))

  //Expose interface
  return {
    isOverDropZone,
    chooseFiles: open,
  }
}