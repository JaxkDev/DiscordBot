# Plugin libs
Ordinarily plugins would use virions, and then they get injected via poggit etc

However, because these libs are not virions but fully fledged composer libs and this plugin cannot be put on poggit CI/Release it is easier to manually shade them.

**EACH NAMESPACE HAS ITS OWN LICENSE.**