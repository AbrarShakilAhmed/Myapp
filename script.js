// Advanced JavaScript for Enhanced Interactivity

document.addEventListener('DOMContentLoaded', function () {
  // Navbar Scroll Effect
  const navbar = document.querySelector('.navbar')
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled')
    } else {
      navbar.classList.remove('scrolled')
    }
  })

  // Smooth Scrolling for Navigation Links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute('href'))
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        })
      }
    })
  })

  // Form Validation and Enhancement
  const forms = document.querySelectorAll('form')
  forms.forEach((form) => {
    form.addEventListener('submit', function (e) {
      const inputs = form.querySelectorAll('input, textarea')
      let isValid = true

      inputs.forEach((input) => {
        if (input.hasAttribute('required') && !input.value.trim()) {
          isValid = false
          input.classList.add('error')
        } else {
          input.classList.remove('error')
        }
      })

      if (!isValid) {
        e.preventDefault()
        showNotification('Please fill in all required fields', 'error')
      }
    })
  })

  // Input Field Enhancement
  const inputs = document.querySelectorAll('input, textarea')
  inputs.forEach((input) => {
    input.addEventListener('focus', function () {
      this.parentElement.classList.add('focused')
    })

    input.addEventListener('blur', function () {
      if (!this.value) {
        this.parentElement.classList.remove('focused')
      }
    })
  })

  // Feature Cards Animation on Scroll
  const observerOptions = {
    threshold: 0.1,
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate')
        observer.unobserve(entry.target)
      }
    })
  }, observerOptions)

  document.querySelectorAll('.feature-card').forEach((card) => {
    observer.observe(card)
  })

  // Loading Animation
  window.addEventListener('load', () => {
    const loader = document.querySelector('.loading')
    if (loader) {
      loader.style.opacity = '0'
      setTimeout(() => {
        loader.style.display = 'none'
      }, 500)
    }
  })

  // Dynamic Counter Animation
  function animateCounter(element, target) {
    let current = 0
    const increment = target / 100
    const duration = 2000 // 2 seconds
    const stepTime = duration / 100

    const timer = setInterval(() => {
      current += increment
      if (current >= target) {
        element.textContent = target
        clearInterval(timer)
      } else {
        element.textContent = Math.floor(current)
      }
    }, stepTime)
  }

  // Initialize counters when they come into view
  const counterObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const target = parseInt(entry.target.getAttribute('data-target'))
          animateCounter(entry.target, target)
          counterObserver.unobserve(entry.target)
        }
      })
    },
    { threshold: 0.5 }
  )

  document.querySelectorAll('.counter').forEach((counter) => {
    counterObserver.observe(counter)
  })

  // Notification System
  function showNotification(message, type = 'success') {
    const notification = document.createElement('div')
    notification.className = `notification ${type}`
    notification.textContent = message

    document.body.appendChild(notification)

    setTimeout(() => {
      notification.classList.add('show')
    }, 100)

    setTimeout(() => {
      notification.classList.remove('show')
      setTimeout(() => {
        notification.remove()
      }, 300)
    }, 3000)
  }

  // Mobile Menu Toggle
  const menuButton = document.querySelector('.menu-toggle')
  const navLinks = document.querySelector('.nav-links')

  if (menuButton && navLinks) {
    menuButton.addEventListener('click', () => {
      navLinks.classList.toggle('active')
      menuButton.classList.toggle('active')
    })
  }

  // Parallax Effect for Hero Section
  const hero = document.querySelector('.hero')
  if (hero) {
    window.addEventListener('scroll', () => {
      const scrolled = window.pageYOffset
      hero.style.backgroundPositionY = scrolled * 0.5 + 'px'
    })
  }

  // Image Lazy Loading
  const lazyImages = document.querySelectorAll('img[data-src]')
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target
        img.src = img.dataset.src
        img.removeAttribute('data-src')
        imageObserver.unobserve(img)
      }
    })
  })

  lazyImages.forEach((img) => imageObserver.observe(img))
})
