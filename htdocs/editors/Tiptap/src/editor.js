import { Editor } from '@tiptap/core'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import StarterKit from '@tiptap/starter-kit'

class ImpressCmsTiptapEditor {
  constructor(config) {
    this.config = config
    this.textarea = document.getElementById(config.textareaId)
    this.toolbar = document.getElementById(config.toolbarId)
    this.element = document.getElementById(config.editorId)

    if (!this.textarea || !this.toolbar || !this.element) {
      return
    }

    this.editor = new Editor({
      element: this.element,
      extensions: [
        StarterKit,
        Link.configure({
          openOnClick: false,
        }),
        Image.configure({
          inline: true,
        }),
      ],
      content: config.document || config.content || '',
      editorProps: {
        attributes: {
          class: 'icms-tiptap-content',
          style: `min-height: ${config.height || '18rem'};`,
        },
      },
      onCreate: ({ editor }) => {
        this.hideTextarea()
        this.syncTextarea(editor)
        this.refreshToolbar()
      },
      onUpdate: ({ editor }) => {
        this.syncTextarea(editor)
        this.refreshToolbar()
      },
      onSelectionUpdate: () => {
        this.refreshToolbar()
      },
    })

    this.bindToolbar()
    this.bindSubmit()
  }

  hideTextarea() {
    this.textarea.style.display = 'none'
  }

  bindToolbar() {
    this.toolbar.querySelectorAll('[data-action]').forEach((button) => {
      button.addEventListener('click', () => {
        const command = this.getCommand(button.dataset.action)

        if (!command) {
          return
        }

        command()
        this.refreshToolbar()
      })
    })
  }

  bindSubmit() {
    const form = this.textarea.closest('form')

    if (!form) {
      return
    }

    form.addEventListener('submit', () => {
      this.syncTextarea(this.editor)
    })
  }

  getCommand(action) {
    const commands = {
      paragraph: () => this.editor.chain().focus().setParagraph().run(),
      bold: () => this.editor.chain().focus().toggleBold().run(),
      italic: () => this.editor.chain().focus().toggleItalic().run(),
      strike: () => this.editor.chain().focus().toggleStrike().run(),
      code: () => this.editor.chain().focus().toggleCode().run(),
      heading1: () => this.editor.chain().focus().toggleHeading({ level: 1 }).run(),
      heading2: () => this.editor.chain().focus().toggleHeading({ level: 2 }).run(),
      heading3: () => this.editor.chain().focus().toggleHeading({ level: 3 }).run(),
      bulletList: () => this.editor.chain().focus().toggleBulletList().run(),
      orderedList: () => this.editor.chain().focus().toggleOrderedList().run(),
      blockquote: () => this.editor.chain().focus().toggleBlockquote().run(),
      codeBlock: () => this.editor.chain().focus().toggleCodeBlock().run(),
      horizontalRule: () => this.editor.chain().focus().setHorizontalRule().run(),
      undo: () => this.editor.chain().focus().undo().run(),
      redo: () => this.editor.chain().focus().redo().run(),
    }

    return commands[action]
  }

  refreshToolbar() {
    const states = {
      paragraph: () => this.editor.isActive('paragraph'),
      bold: () => this.editor.isActive('bold'),
      italic: () => this.editor.isActive('italic'),
      strike: () => this.editor.isActive('strike'),
      code: () => this.editor.isActive('code'),
      heading1: () => this.editor.isActive('heading', { level: 1 }),
      heading2: () => this.editor.isActive('heading', { level: 2 }),
      heading3: () => this.editor.isActive('heading', { level: 3 }),
      bulletList: () => this.editor.isActive('bulletList'),
      orderedList: () => this.editor.isActive('orderedList'),
      blockquote: () => this.editor.isActive('blockquote'),
      codeBlock: () => this.editor.isActive('codeBlock'),
    }

    const canRun = {
      paragraph: () => this.editor.can().chain().focus().setParagraph().run(),
      bold: () => this.editor.can().chain().focus().toggleBold().run(),
      italic: () => this.editor.can().chain().focus().toggleItalic().run(),
      strike: () => this.editor.can().chain().focus().toggleStrike().run(),
      code: () => this.editor.can().chain().focus().toggleCode().run(),
      heading1: () => this.editor.can().chain().focus().toggleHeading({ level: 1 }).run(),
      heading2: () => this.editor.can().chain().focus().toggleHeading({ level: 2 }).run(),
      heading3: () => this.editor.can().chain().focus().toggleHeading({ level: 3 }).run(),
      bulletList: () => this.editor.can().chain().focus().toggleBulletList().run(),
      orderedList: () => this.editor.can().chain().focus().toggleOrderedList().run(),
      blockquote: () => this.editor.can().chain().focus().toggleBlockquote().run(),
      codeBlock: () => this.editor.can().chain().focus().toggleCodeBlock().run(),
      horizontalRule: () => this.editor.can().chain().focus().setHorizontalRule().run(),
      undo: () => this.editor.can().chain().focus().undo().run(),
      redo: () => this.editor.can().chain().focus().redo().run(),
    }

    this.toolbar.querySelectorAll('[data-action]').forEach((button) => {
      const action = button.dataset.action

      if (states[action]) {
        button.classList.toggle('is-active', states[action]())
      }

      if (canRun[action]) {
        button.disabled = !canRun[action]()
      }
    })
  }

  syncTextarea(editor) {
    this.textarea.value = editor.getHTML()
  }
}

function create(config) {
  return new ImpressCmsTiptapEditor(config)
}

const bridge = window.ImpressCmsTiptap || { queue: [] }

bridge.create = create
bridge.enqueue = function enqueue(config) {
  this.queue.push(config)
}
bridge.flush = function flush() {
  while (this.queue.length > 0) {
    create(this.queue.shift())
  }
}

window.ImpressCmsTiptap = bridge

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.ImpressCmsTiptap.flush()
  })
} else {
  window.ImpressCmsTiptap.flush()
}
