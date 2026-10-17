import io,json,re
d=json.load(io.open('tareas.json',encoding='utf-8'))
ACC={"confirmacion":"confirmación","trafico":"tráfico","legitimo":"legítimo","logica":"lógica","dominio":"dominio","exito":"éxito","metodo":"método","cache":"caché","pequeño":"pequeño","guia":"guía","ingles":"inglés","espanol":"español","prestamo":"préstamo","codigo":"código","sesion":"sesión","sesiones":"sesiones","produccion":"producción","conexion":"conexión","version":"versión","configuracion":"configuración","modulo":"módulo","modulos":"módulos","despues":"después","tambien":"también","pagina":"página","paginas":"páginas","dia":"día","dias":"días","numero":"número","automatico":"automático","automatica":"automática","replica":"réplica","caida":"caída","caidas":"caídas","rapido":"rápido","publico":"público","publica":"pública","publicas":"públicas","migracion":"migración","validacion":"validación","restauracion":"restauración","decision":"decisión","ultimo":"último","ultima":"última","estan":"están","aqui":"aquí","informacion":"información","medicion":"medición","notificacion":"notificación","aplicacion":"aplicación","seccion":"sección","funcion":"función","opcion":"opción","administracion":"administración","organizacion":"organización","invitacion":"invitación","verificacion":"verificación","integracion":"integración","peticion":"petición","camara":"cámara","movil":"móvil","tecnica":"técnica","limite":"límite","telefono":"teléfono","revision":"revisión","instalacion":"instalación","solucion":"solución","creacion":"creación","actualizacion":"actualización","autenticacion":"autenticación","aprobacion":"aprobación","documentacion":"documentación","descripcion":"descripción","boton":"botón","menu":"menú","imagenes":"imágenes","critico":"crítico","logica":"lógica","metricas":"métricas","basico":"básico","minimo":"mínimo","maximo":"máximo","unico":"único","unica":"única","haptica":"háptica","pestañas":"pestañas","contraseña":"contraseña","recuperacion":"recuperación","generacion":"generación","edicion":"edición","proximo":"próximo","proximos":"próximos","proxima":"próxima","rapida":"rápida","electronico":"electrónico","calificacion":"calificación","mas":None,"sincronizacion":"sincronización","precalentar":"precalentar","grafico":"gráfico","catalogo":"catálogo","comun":"común","jamas":"jamás","segun":"según","ademas":"además","asi":"así","tambien":"también","via":"vía","util":"útil","facil":"fácil","dificil":"difícil","debil":"débil","titulo":"título","titulos":"títulos","analisis":"análisis","energia":"energía","categoria":"categoría","estandar":"estándar","video":"video","envio":"envío","vacio":"vacío","vacios":"vacíos","lider":"líder","heroe":"héroe","pais":"país","raiz":"raíz","algun":"algún","ningun":"ningún","quizas":"quizás","dinamicas":"dinámicas","dinamico":"dinámico","huerfano":"huérfano","huerfanos":"huérfanos","indice":"índice","indices":"índices","periodico":"periódico","estadisticas":"estadísticas","practica":"práctica","fisico":"físico","fisica":"física","musica":"música","credito":"crédito","rapidos":"rápidos","optimista":"optimista","semaforo":"semáforo","tactil":"táctil","aun":None,"esta":None,"cuando":None,"como":None,"que":None,"donde":None,"por que":None,"cual":None}
def acc(s):
    def f(m):
        w=m.group(0); r=ACC.get(w.lower())
        if not r: return w
        return r[0].upper()+r[1:] if w[0].isupper() else r
    s=re.sub(r"[A-Za-zñÑ]+",f,s)
    s=re.sub(r"¿Que ","¿Qué ",s); s=re.sub(r"¿Cuando ","¿Cuándo ",s); s=re.sub(r"¿Donde ","¿Dónde ",s); s=re.sub(r"\bpor que\b","por qué",s)
    s=re.sub(r"\bya esta\b","ya está",s)
    return s
def tidy(s):
    s=re.sub(r"\s*\(=\s*[A-Z][\w.]*(?:\s*\+\s*[A-Z][\w.]*)*\)","",s)
    s=re.sub(r"^[;,:.\s]+","",s).strip()
    if s.startswith("("):
        j=s.find(")")
        if j>0: s=(s[1:j]+s[j+1:]).strip()
    if s.count(")")>s.count("("):
        j=s.find(")"); s=(s[:j]+s[j+1:]).strip()
    return s
for t in d["tareas"]:
    if len(t["titulo"])<14 and t["desc"]:
        full=t["titulo"]+": "+t["desc"]
        m=re.search(r" \(|\. ",full[20:])
        cut=20+m.start() if m and 20+m.start()<=120 else None
        if cut: t["titulo"],t["desc"]=full[:cut].strip(), full[cut:].lstrip(". ").strip()
        elif len(full)<=120: t["titulo"],t["desc"]=full,""
    t["titulo"]=acc(tidy(t["titulo"])); t["desc"]=acc(tidy(t["desc"]))
    if t["desc"] and not re.match(r"^\S*[_./:]",t["desc"]): t["desc"]=t["desc"][0].upper()+t["desc"][1:]
SECN={1: 'Plan de salida a producción', 2: 'Decisiones tuyas', 3: 'Bugs abiertos', 4: 'Registro, landing y widget', 5: 'Webapp', 6: 'QA presencial', 7: 'App móvil Expo', 8: 'Admin y módulos', 9: 'Manual del organizador', 10: 'Seguridad del staff', 11: 'Deuda técnica', 12: 'Fase 2 y nice to have', 13: 'Después del 6 de diciembre'}
for t in d["tareas"]: t["secT"]=SECN[t["sec"]]
json.dump(d,io.open('tareas-lab.json','w',encoding='utf-8'),ensure_ascii=False)
for t in d["tareas"]:
    if t["mod"]=="decisiones": print("-",t["titulo"],"|",t["desc"][:60])
