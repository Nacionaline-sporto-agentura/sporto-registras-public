jQuery(window).on('elementor/frontend/init', () => {
  class Biip_Off_Canvas extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
      return {
        selectors: {
          button: '.biip-off-canvas-open',
          content: '.biip-off-canvas-content',
          overlay: '.biip-off-canvas-overlay',
          close: '.biip-off-canvas-close'
        }
      };
    } 

    getDefaultElements() {
      const selectors = this.getSettings('selectors');
      return {
        $button: this.$element.find(selectors.button),
        $content: this.$element.find(selectors.content),
        $overlay: this.$element.find(selectors.overlay),
        $close: this.$element.find(selectors.close)
      };
    }

    bindEvents() {
      this.elements.$button.on('click', () => this.openMenu());
      this.elements.$overlay.on('click', () => this.closeMenu());
      this.elements.$close.on('click', () => this.closeMenu());
    }

    openMenu() {
      document.body.classList.add('biip-off-canvas-open');
    }
      closeMenu() {
        document.body.classList.remove('biip-off-canvas-open');
      }
    } 
  
    elementorFrontend.hooks.addAction('frontend/element_ready/biip_off_canvas.default', ($element) => {
      elementorFrontend.elementsHandler.addHandler(Biip_Off_Canvas, {
        $element
      });
    });
  });