export default function useToast() {
  const showToast = (options) => {
    const { content = 'Untitled', autohide = true, delay = 5000 } = options

    const template = `
    <div class="toast align-items-center text-bg-github border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-bs-toggle="toast">
        <div class="d-flex">
            <div class="toast-body">${content}</div>
            <button type="button" class="me-2 m-auto btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    `

    let container = document.querySelector('.toast-container')

    if (!container) {
      container = document.createElement('div')
      container.className = 'toast-container top-0 start-50 translate-middle-x p-5'
      document.body.appendChild(container)
    }

    container.insertAdjacentHTML('beforeend', template)

    const toastElement = container.lastElementChild
    const toast = new bootstrap.Toast(toastElement, {
      autohide: autohide,
      delay: delay,
    })

    toast.show()
  }

  return { showToast }
}
